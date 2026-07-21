<?php

namespace MailMug\PHPUnit;

final class MailMugClient
{
    private string $apiKey;

    private string $inboxId;

    private string $baseUrl;

    private int $timeout;

    public function __construct(
        ?string $apiKey = null,
        ?string $inboxId = null,
        ?string $baseUrl = null,
        ?int $timeout = null
    ) {
        $this->apiKey = $apiKey
            ?? getenv('MAILMUG_API_KEY')
            ?: '';

        $this->inboxId = $inboxId
            ?? getenv('MAILMUG_INBOX_ID')
            ?: '';

        $this->baseUrl = rtrim(
            $baseUrl
                ?? getenv('MAILMUG_API_URL')
                ?: 'https://mailmug.net/api',
            '/'
        );

        $this->timeout = $timeout
            ?? (int) (
                getenv('MAILMUG_TIMEOUT')
                ?: 30
            );

        if ($this->apiKey === '') {
            throw new MailMugException(
                'MAILMUG_API_KEY is not configured.'
            );
        }

        if ($this->inboxId === '') {
            throw new MailMugException(
                'MAILMUG_INBOX_ID is not configured.'
            );
        }
    }

    /**
     * Get emails from the inbox.
     */
    public function emails(
        ?string $subject = null,
        ?string $to = null,
        ?string $from = null,
        int $limit = 5
    ): array {
        $query = array_filter([
            'subject' => $subject,
            'to' => $to,
            'from' => $from,
            'per_page' => $limit,
        ], fn ($value) => $value !== null && $value !== '');

        $path = sprintf(
            '/inboxes/%s/emails?%s',
            $this->inboxId,
            http_build_query($query)
        );

        $response = $this->request(
            'GET',
            $path
        );

        $emails = $response['data']
            ?? $response['emails']
            ?? $response;

        if (!is_array($emails)) {
            return [];
        }

        return array_map(
            fn (array $email) => new MailMugEmail($email),
            $emails
        );
    }

    /**
     * Find an email matching the given conditions.
     */
    public function findEmail(
        ?string $to = null,
        ?string $subject = null,
        ?string $contains = null
    ): ?MailMugEmail {

        $emails = $this->emails(
            subject: $subject,
            to: $to
        );

        foreach ($emails as $email) {
            if ($to !== null && !in_array($to, $email->to(), true)) {
                continue;
            }

            if (
                $subject !== null
                && $email->subject() !== $subject
            ) {
                continue;
            }

            if (
                $contains !== null
                && !$email->contains($contains)
            ) {
                continue;
            }

            return $email;
        }

        return null;
    }

    /**
     * Wait until an email appears.
     */
    public function waitForEmail(
        ?string $to = null,
        ?string $subject = null,
        ?string $contains = null,
        ?int $timeout = null
    ): MailMugEmail {
        $timeout ??= $this->timeout;

        $startedAt = microtime(true);

        do {
            $email = $this->findEmail(
                to: $to,
                subject: $subject,
                contains: $contains
            );

            if ($email !== null) {
                return $email;
            }

            usleep(500_000);

        } while (
            microtime(true) - $startedAt < $timeout
        );

        throw new MailMugException(
            sprintf(
                'Email was not received within %d seconds.',
                $timeout
            )
        );
    }

    /**
     * Make HTTP request.
     */
    private function request(
        string $method,
        string $path,
        ?array $body = null
    ): array {
        $url = $this->baseUrl . $path;

        $ch = curl_init($url);

        if ($ch === false) {
            throw new MailMugException(
                'Unable to initialize cURL.'
            );
        }

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if ($body !== null) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($body, JSON_THROW_ON_ERROR)
            );
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            if (PHP_VERSION_ID < 80500) {
                curl_close($ch);
            }

            throw new MailMugException(
                "MailMug API request failed: {$error}"
            );
        }

        $statusCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        $decoded = json_decode(
            $response,
            true
        );

        if (
            $statusCode < 200
            || $statusCode >= 300
        ) {
            throw new MailMugException(
                sprintf(
                    'MailMug API returned HTTP %d for %s: %s %s',
                    $statusCode,
                    $url,
                    $method,
                    $response
                )
            );
        }

        if (!is_array($decoded)) {
            throw new MailMugException(
                'Invalid JSON response from MailMug API.'
            );
        }

        return $decoded;
    }

    /**
     * 
     * Delete an email from the inbox.
     */
    public function deleteEmail(
        string|int $emailId
    ): void {
        $this->request(
            'DELETE',
            sprintf(
                '/inboxes/%s/emails/%s',
                $this->inboxId,
                $emailId
            )
        );
    }
}