<?php

return [
    'organization_name' => env('APP_ORGANIZATION_NAME', 'FINTEC Digital Skills Academy'),
    'timezone' => env('RECRUITMENT_TIMEZONE', 'Africa/Lagos'),

    // Character limit for the "Tell us about yourself" section.
    'about_max_length' => (int) env('RECRUITMENT_ABOUT_MAX', 4000),

    // Upload constraints shared by validation, the wizard and the admin document screen.
    'max_upload_mb' => (int) env('RECRUITMENT_MAX_UPLOAD_MB', 5),
    'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],

    // Default document requirements applied to newly created positions.
    'default_documents' => [
        ['key' => 'cv', 'label' => 'Curriculum Vitae (CV)'],
        ['key' => 'passport', 'label' => 'Passport Photograph'],
        ['key' => 'certificates', 'label' => 'Educational Certificates'],
        ['key' => 'other', 'label' => 'Other Supporting Document'],
    ],

    'per_page' => 20,
];
