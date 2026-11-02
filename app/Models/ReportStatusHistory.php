<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportStatusHistory extends Model
{
    use HasFactory, HasUuid;

    /**
     * Disable updated_at since status history is append-only.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'waste_report_id',
        'changed_by',
        'old_status',
        'new_status',
        'notes',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the report associated with this history record.
     *
     * @return BelongsTo<WasteReport, ReportStatusHistory>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(WasteReport::class, 'waste_report_id');
    }

    /**
     * Get the administrator who performed this status change.
     *
     * @return BelongsTo<User, ReportStatusHistory>
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

