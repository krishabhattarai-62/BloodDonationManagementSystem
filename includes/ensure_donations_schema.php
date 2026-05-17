<?php
/**
 * Ensures donations table has columns required by schedule_donation and admin_reserve.
 * Safe to call on every request — only runs ALTER when a column is missing.
 */
function ensureDonationsSchema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $required = [
        'donation_time' => 'TIME NULL AFTER donation_date',
        'blood_group'   => 'VARCHAR(5) NULL AFTER donation_time',
        'units'         => 'INT DEFAULT 1 AFTER blood_group',
    ];

    $stmt = $pdo->query('SHOW COLUMNS FROM donations');
    $existing = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing[$row['Field']] = true;
    }

    foreach ($required as $column => $definition) {
        if (!isset($existing[$column])) {
            $pdo->exec("ALTER TABLE donations ADD COLUMN {$column} {$definition}");
        }
    }
}
