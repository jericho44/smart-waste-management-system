<?php

namespace App\Http\Resources;

use App\Models\ReportImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin ReportImage
 */
class ReportImageResource extends JsonResource
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
            'url' => Storage::disk('public')->url($this->image_path),
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'file_size' => (int) $this->file_size,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
