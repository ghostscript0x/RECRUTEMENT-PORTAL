<?php

namespace Tests\Feature;

use Tests\TestCase;

class MailConfigurationTest extends TestCase
{
    public function test_gmail_mailer_uses_configurable_gmail_smtp_settings(): void
    {
        $gmail = config('mail.mailers.gmail');

        $this->assertSame('smtp', $gmail['transport']);
        $this->assertSame('smtp.gmail.com', $gmail['host']);
        $this->assertSame(587, $gmail['port']);
        $this->assertSame('smtp', $gmail['scheme']);
        $this->assertSame('smtp', config('mail.mailers.smtp.transport'));
    }
}
