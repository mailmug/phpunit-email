<?php

namespace MailMug\PHPUnit;

final class MailMugEmail
{
    public function __construct(
        public readonly array $data
    ) {
    }

    public function id(): mixed
    {
        return $this->data['id'] ?? null;
    }

    public function subject(): string
    {
        return (string) ($this->data['subject'] ?? '');
    }

    public function from(): string
    {
        return (string) (
            $this->data['from']
            ?? $this->data['from_email']
            ?? ''
        );
    }

    public function to(): array
    {
        $to = $this->data['to']
            ?? $this->data['to_email']
            ?? [];

        if (is_string($to)) {
            return [$to];
        }

        return is_array($to) ? $to : [];
    }

    public function html(): string
    {
        return (string) (
            $this->data['html']
            ?? $this->data['html_body']
            ?? ''
        );
    }

    public function text(): string
    {
        return (string) (
            $this->data['text']
            ?? $this->data['text_body']
            ?? $this->data['body']
            ?? ''
        );
    }

    public function body(): string
    {
        return $this->html() . "\n" . $this->text();
    }

    public function headers(): array
    {
        return $this->data['headers'] ?? [];
    }

    public function attachments(): array
    {
        return $this->data['attachments'] ?? [];
    }

    public function contains(string $value): bool
    {
        return str_contains($this->body(), $value);
    }

    public function hasAttachment(string $filename): bool
    {
        foreach ($this->attachments() as $attachment) {
            $name = $attachment['filename']
                ?? $attachment['name']
                ?? '';

            if ($name === $filename) {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}