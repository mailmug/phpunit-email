<?php

declare(strict_types=1);

namespace MailMug\PHPUnit\Tests;

use MailMug\PHPUnit\MailMugAssertions;
use MailMug\PHPUnit\MailMugEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

final class MailMugAssertionsTest extends TestCase
{
    use MailMugAssertions;

    private ?MailMugEmail $testEmail = null;

    protected function tearDown(): void
    {
        if ($this->testEmail !== null) {
            $this->mailmug()->deleteEmail(
                $this->testEmail->id()
            );
        }

        parent::tearDown();
    }

    public function test_it_asserts_email_subject(): void
    {
        $this->sendTestEmail();

        $this->testEmail = $this->assertEmailSent(
            to: 'test@example.com',
            subject: 'PHPUnit Test Email'
        );

        $this->assertEmailSubject(
            $this->testEmail,
            'PHPUnit Test Email'
        );
    }

    public function test_it_asserts_email_contains_text(): void
    {
        $this->sendTestEmail();

        $this->testEmail = $this->assertEmailSent(
            to: 'test@example.com',
            contains: 'Hello from PHPUnit'
        );

        $this->assertEmailContains(
            $this->testEmail,
            'Hello from PHPUnit'
        );
    }

    public function test_it_asserts_email_recipient(): void
    {
        $this->sendTestEmail();

        $this->testEmail = $this->assertEmailSentTo(
            'test@example.com'
        );

        $this->assertEmailRecipient(
            $this->testEmail,
            'test@example.com'
        );
    }

    public function test_it_asserts_email_sender(): void
    {
        $this->sendTestEmail();

        $this->testEmail = $this->assertEmailSent(
            to: 'test@example.com',
            subject: 'PHPUnit Test Email'
        );

        $this->assertEmailFrom(
            $this->testEmail,
            'sender@example.com'
        );
    }

    private function sendTestEmail(): void
    {
        $host = getenv('MAILMUG_SMTP_HOST');
        $port = getenv('MAILMUG_SMTP_PORT') ?: '2525';
        $username = getenv('MAILMUG_SMTP_USERNAME');
        $password = getenv('MAILMUG_SMTP_PASSWORD');

        if (
            $host === false
            || $username === false
            || $password === false
        ) {
            $this->fail(
                'SMTP environment variables are not configured.'
            );
        }

        $transport = Transport::fromDsn(
            sprintf(
                'smtp://%s:%s@%s:%s',
                urlencode(
                    (string) getenv('MAILMUG_SMTP_USERNAME')
                ),
                urlencode(
                    (string) getenv('MAILMUG_SMTP_PASSWORD')
                ),
                getenv('MAILMUG_SMTP_HOST'),
                getenv('MAILMUG_SMTP_PORT') ?: 2525
            )
        );

        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('sender@example.com')
            ->to('test@example.com')
            ->subject('PHPUnit Test Email')
            ->text('Hello from PHPUnit');

        $mailer->send($email);
    }
}