<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Rejected = 'REJECTED';

    /**
     * Get all values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Determine if a status transition is permissible.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::InProgress, self::Rejected], true),
            self::InProgress => $target === self::Completed,
            self::Completed, self::Rejected => false,
        };
    }
}

