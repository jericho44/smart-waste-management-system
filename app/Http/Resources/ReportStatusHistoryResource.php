<?php

namespace App\Http\Resources;

use App\Models\ReportStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportStatusHistory
 */
class ReportStatusHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'notes' => $this->notes,
            'changed_by' => $this->whenLoaded('changer', function (): array {
                return [
                    'uuid' => $this->changer?->uuid,
                    'name' => $this->changer?->name,
                    'email' => $this->changer?->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

