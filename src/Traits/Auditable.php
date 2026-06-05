<?php

namespace Auditify\Traits;

use Auditify\Facades\Auditify;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            Auditify::logAction(
                'CREATE',
                class_basename($model),
                class_basename($model) . ' created',
                [],
                $model->toArray(),
                null,
                $model
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $oldValues = [];
            $newValues = [];

            foreach ($changes as $field => $newValue) {
                if (in_array($field, ['updated_at'])) {
                    continue;
                }
                $oldValues[$field] = $model->getOriginal($field);
                $newValues[$field] = $newValue;
            }

            if (empty($newValues)) {
                return;
            }

            Auditify::logAction(
                'UPDATE',
                class_basename($model),
                class_basename($model) . ' updated',
                $oldValues,
                $newValues,
                null,
                $model
            );
        });

        static::deleted(function ($model) {
            Auditify::logAction(
                'DELETE',
                class_basename($model),
                class_basename($model) . ' deleted',
                $model->toArray(),
                [],
                null,
                $model
            );
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                Auditify::logAction(
                    'RESTORE',
                    class_basename($model),
                    class_basename($model) . ' restored',
                    [],
                    $model->toArray(),
                    null,
                    $model
                );
            });
        }
    }
}
