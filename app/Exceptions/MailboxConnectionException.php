<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MailboxConnectionException extends Exception
{
    public function __construct(
        string $message = 'Mailbox connection failed',
        int $code = 0,
        ?Exception $previous = null,
        public readonly ?string $connectionType = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function smtpConnectionFailed(string $details): self
    {
        return new self("SMTP connection failed: {$details}", 500, null, 'smtp');
    }

    public static function imapConnectionFailed(string $details): self
    {
        return new self("IMAP connection failed: {$details}", 500, null, 'imap');
    }

    public static function authenticationFailed(string $type): self
    {
        return new self("{$type} authentication failed. Please check your credentials.", 401, null, strtolower($type));
    }

    public static function timeout(string $type): self
    {
        return new self("{$type} connection timed out.", 408, null, strtolower($type));
    }
}
