<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logChange('created', $model);
        });

        static::updated(function ($model) {
            self::logChange('updated', $model);
        });

        static::deleted(function ($model) {
            self::logChange('deleted', $model);
        });
    }

    protected static function logChange($action, $model)
    {
        $oldValues = $action === 'created' ? null : $model->getOriginal();
        $newValues = $model->getAttributes();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
