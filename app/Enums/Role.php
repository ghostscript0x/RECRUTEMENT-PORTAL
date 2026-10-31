<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case RecruitmentAdmin = 'recruitment_admin';
    case Reviewer = 'reviewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::RecruitmentAdmin => 'Recruitment Administrator',
            self::Reviewer => 'Application Reviewer',
        };
    }

    /**
     * Central permission map. Every admin ability is defined here once and
     * enforced on the backend through Gates (see AppServiceProvider).
     *
     * @return list<string>
     */
    public function abilities(): array
    {
        $review = ['dashboard.view', 'applications.view', 'applications.notes'];

        return match ($this) {
            self::SuperAdmin => ['*'],
            self::RecruitmentAdmin => [...$review, 'applications.status', 'applications.assign', 'positions.manage', 'exports.run', 'templates.manage', 'audit.view', 'interviews.manage'],
            self::Reviewer => $review,
        };
    }
}
