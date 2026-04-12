<?php

declare(strict_types=1);

$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found at {$sqlitePath}\n");
    exit(1);
}

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mysql = new PDO('mysql:host=localhost;port=3306;dbname=dgezuyer_elgon;charset=utf8mb4', 'root', '');
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqliteTables = $sqlite
    ->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
    ->fetchAll(PDO::FETCH_COLUMN);

$mysqlTables = $mysql->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$mysqlTableSet = array_flip($mysqlTables);

$skipTables = ['migrations'];

$mysql->exec('SET FOREIGN_KEY_CHECKS=0');

$totalInserted = 0;
foreach ($sqliteTables as $table) {
    if (in_array($table, $skipTables, true) || !isset($mysqlTableSet[$table])) {
        continue;
    }

    $sqliteCols = $sqlite->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
    $sqliteColNames = array_map(static fn(array $c): string => $c['name'], $sqliteCols);

    $mysqlCols = $mysql->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    $mysqlColNames = array_map(static fn(array $c): string => $c['Field'], $mysqlCols);

    $cols = array_values(array_intersect($mysqlColNames, $sqliteColNames));
    if (!$cols) {
        continue;
    }

    $quotedCols = implode(', ', array_map(static fn(string $c): string => "`{$c}`", $cols));
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));

    $mysql->exec("DELETE FROM `{$table}`");

    $selectStmt = $sqlite->query("SELECT {$quotedCols} FROM `{$table}`");
    $insertStmt = $mysql->prepare("INSERT INTO `{$table}` ({$quotedCols}) VALUES ({$placeholders})");

    $count = 0;
    while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
        $values = [];
        foreach ($cols as $col) {
            $values[] = $row[$col];
        }
        $insertStmt->execute($values);
        $count++;
    }

    $totalInserted += $count;
    echo "{$table}: {$count} rows\n";
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=1');

echo "TOTAL_INSERTED={$totalInserted}\n";
