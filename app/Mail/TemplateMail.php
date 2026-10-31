<?php

namespace App\Mail;

use App\Models\AcademySetting;
use App\Models\Application;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * One mailable serves every notification: the active email template is loaded
 * by key, placeholders are replaced, and the delivery is recorded in email_logs.
 */
class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $templateKey,
        public Application $application,
        public array $extra = [],
    ) {}

    public function envelope(): Envelope
    {
        $template = $this->template();
        $subject = $this->isCustomInterviewInvitation()
            ? (string) $this->extra['interview_email_subject']
            : ($template
                ? $this->renderTemplateContent($template->subject)
                : 'Application update — '.$this->organizationName());

        return new Envelope(
            from: new Address(config('mail.from.address'), $this->organizationName()),
            subject: str_replace(["\r", "\n"], ' ', $subject),
        );
    }

    public function content(): Content
    {
        $template = $this->template();
        $body = $this->isCustomInterviewInvitation()
            ? $this->renderInterviewInvitation()
            : ($template
                ? $this->renderTemplateContent($template->body, true)
                : '<p>We have an update about your application. Please contact our recruitment team if you need help.</p>');

        return new Content(
            view: 'emails.template',
            with: [
                'academyName' => $this->organizationName(),
                'body' => $body,
                'supportEmail' => AcademySetting::query()->value('support_email'),
                'supportPhone' => preg_replace('/[^0-9+]/', '', AcademySetting::query()->value('support_phone') ?? ''),
            ],
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: ['X-Mailer' => 'RecruitmentPortal']);
    }

    /** @return array<string, string> */
    public function placeholders(): array
    {
        return array_merge([
            'applicant_name' => $this->application->full_name,
            'application_reference' => $this->application->reference,
            'position_title' => $this->application->position?->title ?? '-',
            'application_status' => $this->application->statusEnum()->label(),
            'organization_name' => $this->organizationName(),
            'tracking_url' => route('tracking.show', ['reference' => $this->application->reference]),
            'submitted_at' => $this->application->submitted_at?->format('d M Y, h:i A') ?? '',
        ], $this->extra);
    }

    private ?EmailTemplate $resolved = null;

    private ?string $resolvedOrganizationName = null;

    private function template(): ?EmailTemplate
    {
        if ($this->isCustomInterviewInvitation()) {
            return null;
        }

        return $this->resolved ??= EmailTemplate::where('key', $this->templateKey)->first();
    }

    private function organizationName(): string
    {
        return $this->resolvedOrganizationName ??= AcademySetting::query()->value('organization_name')
            ?: config('recruitment.organization_name');
    }

    private function renderTemplateContent(string $body, bool $escapeHtml = false): string
    {
        $replacements = $this->placeholders();

        return str_replace(
            array_map(fn ($key) => '{{'.$key.'}}', array_keys($replacements)),
            array_map(
                fn ($value) => $escapeHtml ? e($value) : strip_tags($value),
                array_values($replacements),
            ),
            $body,
        );
    }

    private function isCustomInterviewInvitation(): bool
    {
        return $this->templateKey === 'interview'
            && is_string($this->extra['interview_email_subject'] ?? null)
            && is_string($this->extra['interview_email_message'] ?? null);
    }

    private function renderInterviewInvitation(): string
    {
        $location = e((string) ($this->extra['interview_location'] ?? ''));
        $url = filter_var((string) ($this->extra['interview_location'] ?? ''), FILTER_VALIDATE_URL);
        $scheme = is_string($url) ? parse_url($url, PHP_URL_SCHEME) : null;

        if (is_string($url) && in_array($scheme, ['http', 'https'], true)) {
            $location = '<a href="'.e($url).'">'.$location.'</a>';
        }

        $body = '<p>Dear '.e($this->application->full_name).',</p>'
            .'<p>'.nl2br(e($this->extra['interview_email_message'])).'</p>'
            .'<p><strong>Interview:</strong> '.e((string) ($this->extra['interview_mode'] ?? '')).'<br>'
            .'<strong>Date:</strong> '.e((string) ($this->extra['interview_date'] ?? '')).'<br>'
            .'<strong>Time:</strong> '.e((string) ($this->extra['interview_time'] ?? '')).' '
            .e((string) ($this->extra['interview_timezone'] ?? '')).'<br>'
            .'<strong>Details:</strong> '.$location.'</p>';

        if (filled($this->extra['interview_instructions'] ?? null)) {
            $body .= '<p><strong>Additional instructions:</strong><br>'
                .nl2br(e($this->extra['interview_instructions'])).'</p>';
        }

        $body .= '<p>Application reference: '.e($this->application->reference).'.</p>'
            .'<p>If you need a different time, <a href="'.e((string) ($this->extra['reschedule_url'] ?? '')).'">'
            .'request a reschedule</a> and share the reason. Your current interview time remains active until we confirm a change.</p>';

        return $body;
    }
}
