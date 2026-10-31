<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

/**
 * Application status notifications use templates; interview invitations are
 * composed for each candidate from the interview workflow.
 */
class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'submitted' => [
                'name' => 'Application Received',
                'subject' => 'Application Received — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>We have received your application for <strong>{{position_title}}</strong>.</p>'
                    .'<p><strong>Reference number:</strong> {{application_reference}}<br>'
                    .'<strong>Submitted:</strong> {{submitted_at}}</p>'
                    .'<p>Please keep your reference number safe — you will need it together with this email address to '
                    .'<a href="{{tracking_url}}">track your application</a>.</p>'
                ),
            ],
            'under_review' => [
                'name' => 'Application Under Review',
                'subject' => 'Your Application Is Under Review — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Your application for <strong>{{position_title}}</strong> ({{application_reference}}) is now under review.</p>'
                    .'<p>We will contact you as soon as there is an update. You may check the current status on our '
                    .'<a href="{{tracking_url}}">tracking page</a>.</p>'
                ),
            ],
            'shortlisted' => [
                'name' => 'Shortlisting Notification',
                'subject' => 'You Have Been Shortlisted — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Congratulations! Your application for <strong>{{position_title}}</strong> '
                    .'({{application_reference}}) has been shortlisted.</p>'
                    .'<p>Further instructions will follow shortly. Please keep your reference number available.</p>'
                ),
            ],
            'final_review' => [
                'name' => 'Final Review',
                'subject' => 'Final Review Update — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Your application for <strong>{{position_title}}</strong> ({{application_reference}}) '
                    .'is at the final review stage.</p>'
                ),
            ],
            'selected' => [
                'name' => 'Selection Notification',
                'subject' => 'Congratulations — You Have Been Selected — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Congratulations! You have been selected for <strong>{{position_title}}</strong>.</p>'
                    .'<p>Our team will contact you with the next steps. Your reference number is {{application_reference}}.</p>'
                ),
            ],
            'rejected' => [
                'name' => 'Rejection Notification',
                'subject' => 'Update on Your Application — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Thank you for taking the time to apply for <strong>{{position_title}}</strong>.</p>'
                    .'<p>After careful consideration, we regret to inform you that your application '
                    .'({{application_reference}}) has not been successful on this occasion.</p>'
                    .'<p>We encourage you to apply for future vacancies that match your qualifications.</p>'
                ),
            ],
            'more_info_required' => [
                'name' => 'Additional Information Requested',
                'subject' => 'Additional Information Required — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>We need a little more information to process your application for '
                    .'<strong>{{position_title}}</strong> ({{application_reference}}).</p>'
                    .'<p>Please reply to this message or contact our recruitment team with the requested details.</p>'
                ),
            ],
            'withdrawn' => [
                'name' => 'Application Withdrawn',
                'subject' => 'Application Withdrawn — {{organization_name}}',
                'body' => $this->wrap(
                    '<p>Dear {{applicant_name}},</p>'
                    .'<p>Your application for <strong>{{position_title}}</strong> ({{application_reference}}) '
                    .'has been withdrawn. You may apply again for any open position.</p>'
                ),
            ],
        ];

        foreach ($templates as $key => $template) {
            EmailTemplate::updateOrCreate(['key' => $key], $template + ['enabled' => true]);
        }
    }

    private function wrap(string $body): string
    {
        return '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#1f2937">'
            .$body
            .'<p style="color:#6b7280;font-size:13px">{{organization_name}} Recruitment Team</p></div>';
    }
}
