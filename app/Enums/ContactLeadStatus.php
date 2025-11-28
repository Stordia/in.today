<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactLeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::ProposalSent => 'Proposal Sent',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Spam => 'Spam',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted => 'primary',
            self::Qualified => 'primary',
            self::ProposalSent => 'warning',
            self::Won => 'success',
            self::Lost => 'danger',
            self::Spam => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'heroicon-o-inbox',
            self::Contacted => 'heroicon-o-phone',
            self::Qualified => 'heroicon-o-star',
            self::ProposalSent => 'heroicon-o-document-text',
            self::Won => 'heroicon-o-check-circle',
            self::Lost => 'heroicon-o-x-circle',
            self::Spam => 'heroicon-o-trash',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::Contacted, self::Qualified, self::ProposalSent], true);
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost, self::Spam], true);
    }

    public function canConvert(): bool
    {
        return in_array($this, [self::Qualified, self::ProposalSent], true);
    }
}
