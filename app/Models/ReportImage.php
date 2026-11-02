<?php

namespace App\Models;

use App\Models\Concerns\HasAudit;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportImage extends Model
{
    use HasAudit, HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'waste_report_id',
        'image_path',
        'original_filename',
        'mime_type',
        'file_size',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the report this image belongs to.
     *
     * @return BelongsTo<WasteReport, ReportImage>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(WasteReport::class, 'waste_report_id');
    }
}

