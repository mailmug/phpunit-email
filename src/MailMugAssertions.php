<?php

namespace MailMug\PHPUnit;

use PHPUnit\Framework\Assert;

trait MailMugAssertions
{
    private ?MailMugClient $mailMugClient = null;

    /**
     * Get the MailMug client.
     */
    public function mailmug(): MailMugClient
    {
        if ($this->mailMugClient === null) {
            $this->mailMugClient = new MailMugClient();
        }

        return $this->mailMugClient;
    }

    /**
     * Set a custom MailMug client.
     */
    public function setMailMugClient(
        MailMugClient $client
    ): void {
        $this->mailMugClient = $client;
    }

    /**
     * Assert that an email is sent.
     */
    public function assertEmailSent(
        ?string $to = null,
        ?string $subject = null,
        ?string $contains = null,
        ?int $timeout = null
    ): MailMugEmail {
        return $this->mailmug()->waitForEmail(
            to: $to,
            subject: $subject,
            contains: $contains,
            timeout: $timeout
        );
    }

    /**
     * Assert email sent to recipient.
     */
    public function assertEmailSentTo(
        string $email,
        ?int $timeout = null
    ): MailMugEmail {
        return $this->assertEmailSent(
            to: $email,
            timeout: $timeout
        );
    }

    /**
     * Assert email subject.
     */
    public function assertEmailSubject(
        MailMugEmail $email,
        string $subject
    ): void {
        Assert::assertSame(
            $subject,
            $email->subject(),
            'Email subject does not match.'
        );
    }

    /**
     * Assert email body contains text.
     */
    public function assertEmailContains(
        MailMugEmail $email,
        string $text
    ): void {
        Assert::assertTrue(
            $email->contains($text),
            sprintf(
                'Email does not contain: "%s"',
                $text
            )
        );
    }

    /**
     * Assert recipient.
     */
    public function assertEmailRecipient(
        MailMugEmail $email,
        string $recipient
    ): void {
        Assert::assertContains(
            $recipient,
            $email->to(),
            'Email recipient does not match.'
        );
    }

    /**
     * Assert sender.
     */
    public function assertEmailFrom(
        MailMugEmail $email,
        string $sender
    ): void {
        Assert::assertSame(
            $sender,
            $email->from(),
            'Email sender does not match.'
        );
    }

    /**
     * Assert email has attachment.
     */
    public function assertEmailHasAttachment(
        MailMugEmail $email,
        string $filename
    ): void {
        Assert::assertTrue(
            $email->hasAttachment($filename),
            sprintf(
                'Email does not have attachment: "%s"',
                $filename
            )
        );
    }

    /**
     * Assert exact email count.
     */
    public function assertEmailCount(
        int $expected,
        ?string $subject = null,
        ?string $to = null
    ): void {
        $emails = $this->mailmug()->emails(
            subject: $subject,
            to: $to
        );

        Assert::assertCount(
            $expected,
            $emails,
            'Email count does not match.'
        );
    }
}