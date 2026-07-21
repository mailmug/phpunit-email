# MailMug PHPUnit

[![Latest Version](https://img.shields.io/packagist/v/mailmug/phpunit.svg)](https://packagist.org/packages/mailmug/phpunit)
[![PHP Version](https://img.shields.io/packagist/php-v/mailmug/phpunit.svg)](https://packagist.org/packages/mailmug/phpunit)
[![License](https://img.shields.io/github/license/mailmug/phpunit/phpunit.svg)](https://github.com/mailmug/phpunit/blob/main/LICENSE)

PHPUnit assertions for testing emails sent through [MailMug](https://mailmug.net) SMTP Sandbox.

Test your application emails in PHPUnit without sending real emails.

## Why MailMug PHPUnit?

Email tests often need more than checking whether a mail method was called.

With MailMug PHPUnit, you can:

* Send a real email through your application's mail configuration
* Capture it in a MailMug SMTP Sandbox inbox
* Assert that the email was delivered
* Verify the subject, recipient, sender, and body
* Test real email content and integration behavior

## Requirements

* PHP `8.1` or higher
* PHPUnit `10`, `11`, `12`, or `13`
* A MailMug account and API key

## Installation

Install the package with Composer:

```bash
composer require --dev mailmug/phpunit
```

## Configuration

Set your MailMug API key and inbox ID as environment variables:

```env
MAILMUG_API_KEY=your-api-key
MAILMUG_INBOX_ID=your-inbox-id
```

You can find your API key and inbox ID in your MailMug dashboard.

## Basic Usage

```php
use MailMug\PHPUnit\MailMugAssertions;

final class WelcomeEmailTest extends TestCase
{
    use MailMugAssertions;

    public function test_welcome_email_is_sent(): void
    {
        // Trigger your application email here.

        $this->assertEmailSent(
            apiKey: getenv('MAILMUG_API_KEY'),
            inboxId: getenv('MAILMUG_INBOX_ID'),
            subject: 'Welcome to our application'
        );
    }
}
```

## Available Assertions

### Assert an email was sent

```php
$this->assertEmailSent(
    apiKey: $apiKey,
    inboxId: $inboxId
);
```

### Assert an email with a specific subject

```php
$this->assertEmailSent(
    apiKey: $apiKey,
    inboxId: $inboxId,
    subject: 'Welcome to our application'
);
```

### Assert an email was sent to a recipient

```php
$this->assertEmailSentTo(
    apiKey: $apiKey,
    inboxId: $inboxId,
    recipient: 'user@example.com'
);
```

### Assert email content

```php
$this->assertEmailContains(
    apiKey: $apiKey,
    inboxId: $inboxId,
    text: 'Welcome to our application'
);
```

> The available assertions depend on the version of the package you are using.

## Example: Testing a Real Email Flow

```php
public function test_password_reset_email(): void
{
    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $this->assertEmailSent(
        apiKey: getenv('MAILMUG_API_KEY'),
        inboxId: getenv('MAILMUG_INBOX_ID'),
        subject: 'Reset Password'
    );
}
```

This tests the actual email flow:

```text
Your application
       │
       ▼
MailMug SMTP Sandbox
       │
       ▼
PHPUnit assertions
```

No real emails are delivered to users.

## SMTP Configuration

Configure your application to send mail through your MailMug SMTP inbox.

Example:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailmug.net
MAIL_PORT=2525
MAIL_USERNAME=your-inbox-username
MAIL_PASSWORD=your-inbox-password
MAIL_ENCRYPTION=null
```

Your SMTP credentials are available in your MailMug dashboard.

## CI/CD

MailMug PHPUnit works well with continuous integration.

Example GitHub Actions workflow:

```yaml
name: Tests

on:
  push:
  pull_request:

jobs:
  tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring
          coverage: none

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run tests
        env:
          MAILMUG_API_KEY: ${{ secrets.MAILMUG_API_KEY }}
          MAILMUG_INBOX_ID: ${{ secrets.MAILMUG_INBOX_ID }}
        run: vendor/bin/phpunit
```

Add your MailMug API key to your repository's GitHub Actions secrets.

## PHPUnit Compatibility

| PHPUnit    | Supported |
| ---------- | --------- |
| PHPUnit 10 | ✅         |
| PHPUnit 11 | ✅         |
| PHPUnit 12 | ✅         |
| PHPUnit 13 | ✅         |

## Why Use MailMug?

Traditional email mocking only verifies that your code called a mailer.

MailMug lets you test the complete email delivery flow:

```text
Application
    ↓
SMTP
    ↓
MailMug Inbox
    ↓
PHPUnit Assertions
```

This helps catch real integration problems such as:

* Incorrect SMTP configuration
* Invalid recipients
* Incorrect email subjects
* Missing email content
* HTML rendering problems
* Email delivery integration failures

## Contributing

Contributions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Add or update tests.
4. Run the test suite:

```bash
composer test
```

5. Submit a pull request.

## License

MailMug PHPUnit is open-source software licensed under the [MIT License](LICENSE).

## Links

* MailMug: https://mailmug.net
* GitHub: https://github.com/mailmug/phpunit
* Packagist: https://packagist.org/packages/mailmug/phpunit
