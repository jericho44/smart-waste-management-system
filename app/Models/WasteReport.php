<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Models\Concerns\HasAudit;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteReport extends Model
{
    use HasAudit, HasFactory, HasUuid, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'waste_category_id',
        'title',
        'description',
        'status',
        'latitude',
        'longitude',
        'address',
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
        'status' => ReportStatus::class,
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user who submitted the report.
     *
     * @return BelongsTo<User, WasteReport>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the report.
     *
     * @return BelongsTo<WasteCategory, WasteReport>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'waste_category_id');
    }

    /**
     * Get all images attached to this report.
     *
     * @return HasMany<ReportImage>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ReportImage::class);
    }

    /**
     * Get the status transition history for this report.
     *
     * @return HasMany<ReportStatusHistory>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class);
    }
}
