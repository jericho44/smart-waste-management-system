<?php

namespace App\DTOs\Reports;

use App\Enums\ReportStatus;

readonly class TransitionStatusDTO
{
    public function __construct(
        public ReportStatus $newStatus,
        public ?string $notes = null,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{status: string, notes?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            newStatus: ReportStatus::from($data['status']),
            notes: isset($data['notes']) ? trim($data['notes']) : null,
        );
    }
}

