<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Single source of truth for the multi-step application form: step order,
 * validation rules, review labels and the view for each step. The controller,
 * the review page and the validation all read from this one class.
 */
class ApplicationWizard
{
    public const REVIEW = 'review';

    /** @var array<int, array{title: string, view: string}> */
    public const STEPS = [
        1 => ['title' => 'Position Selection', 'view' => 'position'],
        2 => ['title' => 'Personal Information', 'view' => 'personal'],
        3 => ['title' => 'Educational Qualifications', 'view' => 'education'],
        4 => ['title' => 'Skills & Competencies', 'view' => 'skills'],
        5 => ['title' => 'Leadership Experience', 'view' => 'leadership'],
        6 => ['title' => 'About Yourself', 'view' => 'about'],
        7 => ['title' => 'Supporting Documents', 'view' => 'documents'],
        8 => ['title' => 'Review & Submit', 'view' => 'review'],
    ];

    public const COUNT = 7; // steps validated before the review step

    /** Field labels used by the review screen. @var array<string, string> */
    public const LABELS = [
        'position_id' => 'Position Applied For',
        'first_name' => 'First Name', 'middle_name' => 'Middle Name', 'last_name' => 'Last Name',
        'email' => 'Email Address', 'phone' => 'Phone Number', 'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'state_of_origin_id' => 'State of Origin', 'origin_lga_id' => 'Local Government Area',
        'residence_state_id' => 'Current State of Residence', 'residence_lga_id' => 'Current LGA',
        'residential_address' => 'Residential Address',
        'highest_qualification' => 'Highest Qualification', 'highest_qualification_other' => 'Other qualification',
        'institution' => 'Institution Name',
        'course_of_study' => 'Course of Study', 'graduation_year' => 'Graduation Year',
        'additional_qualifications' => 'Additional Qualification Details',
        'has_leadership' => 'Leadership Experience',
        'leadership_organization' => 'Organization/Institution',
        'leadership_position' => 'Leadership Position/Role', 'leadership_duration' => 'Duration',
        'leadership_description' => 'Description of Experience',
        'about' => 'Tell Us About Yourself',
    ];

    /** Session key holding the applicant's in-progress draft. */
    public static function draftKey(Position $position): string
    {
        return "application_draft.{$position->id}";
    }

    /** @return array<string, mixed> */
    public static function draft(Request $request, Position $position): array
    {
        return $request->session()->get(self::draftKey($position), []);
    }

    public static function store(Request $request, Position $position, array $data): void
    {
        $draft = self::draft($request, $position);
        $draft['data'] = array_merge($draft['data'] ?? [], $data);
        $request->session()->put(self::draftKey($position), $draft);
    }

    /**
     * Validation rules for a step. Documents are driven by the position's
     * configured requirements, and leadership rules only apply when "Yes".
     *
     * @return array<string, mixed>
     */
    public static function rules(int $step, Request $request, Position $position): array
    {
        return match ($step) {
            1 => [
                'position_id' => ['required', 'integer', Rule::exists('positions', 'id')->where('status', 'published')],
            ],
            2 => [
                'first_name' => ['required', 'string', 'max:100'],
                'middle_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150'],
                'phone' => ['required', 'string', 'max:32'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', Rule::in(['male', 'female'])],
                'state_of_origin_id' => ['nullable', 'integer', Rule::exists('states', 'id')],
                'origin_lga_id' => ['nullable', 'integer', self::lgaRule('state_of_origin_id')],
                'residence_state_id' => ['required', 'integer', Rule::exists('states', 'id')],
                'residence_lga_id' => ['nullable', 'integer', self::lgaRule('residence_state_id')],
                'residential_address' => ['required', 'string', 'max:500'],
            ],
            3 => [
                'highest_qualification' => ['required', Rule::in(self::qualifications())],
                'highest_qualification_other' => ['required_if:highest_qualification,Other', 'nullable', 'string', 'max:100'],
                'institution' => ['nullable', 'string', 'max:200'],
                'course_of_study' => ['nullable', 'string', 'max:200'],
                'graduation_year' => ['nullable', 'integer', 'min:1960', 'max:'.(now()->year + 1)],
                'additional_qualifications' => ['nullable', 'string', 'max:2000'],
            ],
            4 => [
                'skills' => ['required', 'array', 'min:1', 'max:25'],
                'skills.*' => ['required', 'string', 'max:80'],
            ],
            5 => [
                'has_leadership' => ['required', Rule::in(['yes', 'no'])],
                'leadership_organization' => [Rule::requiredIf(fn () => $request->input('has_leadership') === 'yes'), 'string', 'max:200'],
                'leadership_position' => [Rule::requiredIf(fn () => $request->input('has_leadership') === 'yes'), 'string', 'max:200'],
                'leadership_duration' => ['nullable', 'string', 'max:100'],
                'leadership_description' => [Rule::requiredIf(fn () => $request->input('has_leadership') === 'yes'), 'string', 'max:3000'],
            ],
            6 => [
                'about' => ['required', 'string', 'max:'.config('recruitment.about_max_length')],
            ],
            7 => self::documentRules($request, $position),
            default => [],
        };
    }

    /** @return list<string> */
    public static function qualifications(): array
    {
        return ['SSCE', 'NCE', 'ND', 'HND', 'BSc', 'BA', 'MSc', 'MA', 'PhD', 'Other'];
    }

    /**
     * Upload rules generated from the position's required/optional documents.
     *
     * @return array<string, mixed>
     */
    public static function documentRules(Request $request, Position $position): array
    {
        $rules = [];
        $mimes = implode(',', config('recruitment.allowed_mimes'));
        $uploaded = self::draft($request, $position)['files'] ?? [];

        foreach ($position->documentRequirements() as $doc) {
            $key = 'documents.'.$doc['key'];
            $base = ['file', 'mimes:'.$mimes, 'max:'.(config('recruitment.max_upload_mb') * 1024)];
            $isRequired = $doc['required'] && ! isset($uploaded[$doc['key']]);
            $rules[$key] = $isRequired ? ['required', ...$base] : ['nullable', ...$base];
        }

        return $rules;
    }

    /** LGA must belong to the selected state. */
    private static function lgaRule(string $stateField): Exists
    {
        return Rule::exists('local_governments', 'id')->where('state_id', request()->input($stateField));
    }

    /** @return array<int, string> */
    public static function viewData(Position $position, int $step): array
    {
        return [
            'position' => $position,
            'step' => $step,
            'stepTitle' => self::STEPS[$step]['title'],
            'stepView' => 'apply.steps.'.self::STEPS[$step]['view'],
        ];
    }
}
