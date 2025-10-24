<?php
declare(strict_types=1);

namespace App\Services\Audit;

use App\Core\Database;

final class AuditLogger
{
    public function log(
        string $actor,
        string $model,
        string $fieldKey,
        ?string $oldValue,
        ?string $newValue,
        ?string $idRef = null
    ): void {
        $db = Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO audit_log (admin_address, model, field_key, id_ref, old_value, new_value)
             VALUES (:actor, :model, :field, :id_ref, :old, :new)'
        );

        $stmt->execute([
            'actor' => $actor,
            'model' => $model,
            'field' => $fieldKey,
            'id_ref' => $idRef,
            'old' => $oldValue,
            'new' => $newValue,
        ]);
    }
}
