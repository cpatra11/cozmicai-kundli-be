<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Lib\Compatibility;
use App\Lib\Ashtakoota;

// This script compares rule-engine computed Ashtakoota values against legacy CSV values.
// Run: docker compose exec -T jyotish_api php /var/www/api/bin/compatibility_parity_check.php

$dataFile = __DIR__ . '/../data/all_nak_pad_boy_girl.json';
if (!file_exists($dataFile)) {
    fwrite(STDERR, "Missing data file: $dataFile\n");
    exit(1);
}

$json = file_get_contents($dataFile);
$rows = json_decode($json, true);
if (!is_array($rows)) {
    fwrite(STDERR, "Malformed JSON in $dataFile\n");
    exit(1);
}

$mismatches = [];
$total = count($rows);

foreach ($rows as $row) {
    $bn = (int)($row['boy_nakshatra'] ?? 0);
    $bp = (int)($row['boy_paadham'] ?? 0);
    $gn = (int)($row['girl_nakshatra'] ?? 0);
    $gp = (int)($row['girl_paadham'] ?? 0);

    try {
        $rule = Ashtakoota::calculate($bn, $bp, $gn, $gp);
    } catch (\Throwable $e) {
        $mismatches[] = [
            'pair' => "$bn/$bp vs $gn/$gp",
            'error' => $e->getMessage(),
        ];
        continue;
    }

    $legacy = $row['ettu'] ?? [];
    $ruleValues = $rule['values'] ?? [];

    $diff = [];
    foreach ($legacy as $i => $value) {
        $key = array_keys($ruleValues)[$i] ?? null;
        if ($key === null) {
            continue;
        }
        if ((int)$value !== (int)($ruleValues[$key] ?? null)) {
            $diff[$key] = [
                'csv' => (int)$value,
                'rule' => (int)($ruleValues[$key] ?? null),
            ];
        }
    }

    if (!empty($diff)) {
        $mismatches[] = [
            'pair' => "$bn/$bp vs $gn/$gp",
            'diff' => $diff,
        ];
    }
}

fwrite(STDOUT, "Total rows checked: $total\n");
fwrite(STDOUT, "Mismatches found: " . count($mismatches) . "\n");
if (!empty($mismatches)) {
    fwrite(STDOUT, "First 20 mismatches:\n");
    foreach (array_slice($mismatches, 0, 20) as $m) {
        fwrite(STDOUT, json_encode($m) . "\n");
    }
}

exit(count($mismatches) > 0 ? 1 : 0);
