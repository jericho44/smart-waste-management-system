<?php

namespace App\Http\Resources;

use App\Models\WasteReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WasteReport
 */
class WasteReportResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address' => $this->address,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new WasteCategoryResource($this->whenLoaded('category')),
            'images' => ReportImageResource::collection($this->whenLoaded('images')),
            'status_histories' => ReportStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
