<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case FinalReview = 'final_review';
    case Selected = 'selected';
    case Rejected = 'rejected';
    case MoreInfoRequired = 'more_info_required';
    case Withdrawn = 'withdrawn';

    /** The public-facing labels shown to applicants and administrators. */
    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Application Submitted',
            self::UnderReview => 'Under Review',
            self::Shortlisted => 'Shortlisted',
            self::Interview => 'Interview',
            self::FinalReview => 'Final Review',
            self::Selected => 'Selected',
            self::Rejected => 'Rejected',
            self::MoreInfoRequired => 'More Information Required',
            self::Withdrawn => 'Withdrawn',
        };
    }

    /** Tailwind badge classes for the admin dashboard and tracking timeline. */
    public function badge(): string
    {
        return match ($this) {
            self::Submitted => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::UnderReview => 'bg-blue-100 text-blue-700 ring-blue-200',
            self::FinalReview => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
            self::Shortlisted => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Interview => 'bg-violet-100 text-violet-700 ring-violet-200',
            self::Selected => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-700 ring-rose-200',
            self::MoreInfoRequired => 'bg-orange-100 text-orange-700 ring-orange-200',
            self::Withdrawn => 'bg-zinc-100 text-zinc-600 ring-zinc-200',
        };
    }

    /** Whether applicants can no longer expect further progress from this state. */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Selected, self::Rejected, self::Withdrawn], true);
    }

    /** Email template key fired when the application moves into this status. */
    public function emailTemplateKey(): string
    {
        return $this->value;
    }

    /** @return list<self> */
    public static function options(): array
    {
        return self::cases();
    }
}
