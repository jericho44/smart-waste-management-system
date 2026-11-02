<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    /**
     * Boot the audit trait for the model.
     */
    protected static function bootHasAudit(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->created_by) && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function (Model $model): void {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function (Model $model): void {
            if (Auth::check() && in_array('deleted_by', $model->getFillable(), true)) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });
    }
}
