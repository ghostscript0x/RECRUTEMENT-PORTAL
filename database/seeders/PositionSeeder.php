<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Sample published vacancies so the public portal has content on first run.
 * Administrators can edit, close or archive these from the dashboard.
 */
class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $documents = config('recruitment.default_documents');
        $required = array_map(fn ($doc) => $doc + ['required' => true], array_slice($documents, 0, 2));
        $optional = array_map(fn ($doc) => $doc + ['required' => false], array_slice($documents, 2));

        $positions = [
            [
                'title' => 'Administrative Officer',
                'department' => 'Administration',
                'description' => 'The Administrative Officer provides day-to-day administrative support, maintains records and coordinates office activities across departments.',
                'requirements' => "Minimum of a Bachelor's degree in Administration, Management or a related field.\nAt least 2 years of relevant experience.\nExcellent written and oral communication skills.",
                'opens_at' => now()->subWeek()->toDateString(),
                'closes_at' => now()->addMonth()->toDateString(),
            ],
            [
                'title' => 'Accountant',
                'department' => 'Finance',
                'description' => 'The Accountant maintains financial records, prepares reports and supports budgeting and audit processes.',
                'requirements' => "BSc/HND in Accounting, Finance or Economics.\nProfessional certification (ICAN/ACCA) is an advantage.\nProficiency in accounting software.",
                'opens_at' => now()->toDateString(),
                'closes_at' => now()->addMonth()->toDateString(),
            ],
            [
                'title' => 'Software Developer',
                'department' => 'Information Technology',
                'description' => 'The Software Developer designs, builds and maintains internal applications and integrations.',
                'requirements' => "Degree or equivalent experience in Computer Science or a related field.\nProficiency in at least one modern programming language.\nStrong problem-solving skills.",
                'opens_at' => now()->toDateString(),
                'closes_at' => now()->addMonth()->addWeeks(2)->toDateString(),
            ],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['title' => $position['title']],
                $position + [
                    'status' => 'published',
                    'required_documents' => $required,
                    'optional_documents' => $optional,
                ],
            );
        }
    }
}
