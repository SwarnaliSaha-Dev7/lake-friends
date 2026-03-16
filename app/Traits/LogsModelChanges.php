<?php

namespace App\Traits;

use App\Models\LogChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsModelChanges
{
    public static function bootLogsModelChanges()
    {
        // static::updating(function ($model) {
        //     $changes = $model->getDirty();

        //     if (!empty($changes)) {
        //         LogChange::create([
        //             'model' => get_class($model),
        //             'model_id' => $model->id,
        //             'changes' => json_encode($changes),
        //             'updated_by' => auth()->id() ?? null,
        //         ]);
        //     }
        // });

        static::created(function ($model) {
            $model->logChange('created', $model->getAttributes());
        });

        static::updating(function ($model) {
            $changes = $model->getDirty(); // Only changed fields
            if (!empty($changes)) {
                $model->logChange('updated', $changes);
            }
        });

        static::deleting(function ($model) {
            // If using SoftDeletes and it's not a force delete
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->logChange('soft-deleted', $model->getAttributes());
            } else {
                // Hard delete
                $model->logChange('deleted', $model->getAttributes());
            }
        });
    }

    public function logChange($action, $changes)
    {
        $user = Auth::user();
        // $from = 'unknown';

        // if (Auth::guard('admin')->check()) {
        //     $from = 'admin';
        //     $description = 'Admin ' . $action . ' ' . class_basename($this);
        // } elseif (Auth::guard('parent')->check()) {
        //     $from = 'parent';
        //     $description = 'Parent ' . $action . ' own ' . class_basename($this);
        // } else {
        //     $description = ucfirst($action) . ' ' . class_basename($this);
        // }

        if ($user) {
            $userRole = $user->getRoleNames()->first();
            if ($userRole) {
                $description = $userRole . ' ' . $action . ' ' . class_basename($this);
            } else {
                $description = ucfirst($action) . ' ' . class_basename($this);
            }
        } else {
            $userRole = 'System';
            $description = $userRole . ' ' . $action . ' ' . class_basename($this);
        }

        LogChange::create([
            'model' => get_class($this),
            'model_id' => $this->id,
            'action' => $action,
            'changes' => json_encode($changes),
            'updated_by' => $user?->id,
            // 'changed_from' => $from,
            'changed_from' => $userRole,
            'description' => $description,
        ]);
    }
}
