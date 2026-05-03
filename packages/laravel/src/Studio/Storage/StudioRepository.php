<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Storage;

use Illuminate\Support\Facades\File;
use PDO;

final class StudioRepository
{
    private PDO $db;

    public function __construct()
    {
        if (function_exists('app') && app()->runningUnitTests()) {
            $dbPath = ':memory:';
            $isNew = true;
        } else {
            $storagePath = storage_path('app');
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $dbPath = $storagePath . '/fel-studio.sqlite';
            $isNew = !File::exists($dbPath);
        }

        $this->db = new PDO('sqlite:' . $dbPath);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($isNew) {
            $this->migrate();
        }
    }

    private function migrate(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS timeline (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT,
                serie TEXT,
                numero TEXT,
                dte_type TEXT,
                recipient_tax_id TEXT,
                idempotency_key TEXT,
                status TEXT,
                payload JSON,
                error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * @param array<string, mixed> $data
     */
    public function logTransaction(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO timeline (
                uuid, serie, numero, dte_type, recipient_tax_id, 
                idempotency_key, status, payload, error_message, created_at
            ) VALUES (
                :uuid, :serie, :numero, :dte_type, :recipient_tax_id,
                :idempotency_key, :status, :payload, :error_message, :created_at
            )
        ");

        $stmt->execute([
            ':uuid' => $data['uuid'] ?? null,
            ':serie' => $data['serie'] ?? null,
            ':numero' => $data['numero'] ?? null,
            ':dte_type' => $data['dte_type'] ?? null,
            ':recipient_tax_id' => $data['recipient_tax_id'] ?? null,
            ':idempotency_key' => $data['idempotency_key'] ?? null,
            ':status' => $data['status'] ?? 'issued',
            ':payload' => isset($data['payload']) ? json_encode($data['payload']) : null,
            ':error_message' => $data['error_message'] ?? null,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTimeline(): array
    {
        $stmt = $this->db->query("SELECT * FROM timeline ORDER BY created_at DESC LIMIT 100");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON payloads
        foreach ($results as &$row) {
            if (isset($row['payload'])) {
                $row['payload'] = json_decode((string) $row['payload'], true);
            }
        }

        return $results;
    }

    public function clear(): void
    {
        $this->db->exec("DELETE FROM timeline");
    }
}
