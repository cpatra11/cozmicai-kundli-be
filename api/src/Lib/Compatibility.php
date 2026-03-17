<?php

namespace App\Lib;

use App\Lib\Ashtakoota;

class Compatibility
{
    // Data files are stored at the project root under /data (sibling to /src).
    private const CSV_FILE = __DIR__ . '/../../data/all_nak_pad_boy_girl.csv';
    private const JSON_FILE = __DIR__ . '/../../data/all_nak_pad_boy_girl.json';

    // Column indexes taken from the original PyJHora CSV layout
    private const COL_BOY_STAR = 0;
    private const COL_BOY_PAD = 1;
    private const COL_GIRL_STAR = 2;
    private const COL_GIRL_PAD = 3;
    private const COL_VARNA = 4;
    private const COL_VASIYA = 5;
    private const COL_GANA = 6;
    private const COL_STAR = 7;
    private const COL_YONI = 8;
    private const COL_ADHIPATHI = 9;
    private const COL_RASI = 10;
    private const COL_NADI = 11;
    private const COL_SCORE = 12;
    private const COL_MAHENDRA = 13;
    private const COL_VEDHA = 14;
    private const COL_RAJJU = 15;
    private const COL_SHREE = 16;

    /** @var array|null */
    private static $data = null;

    private static function loadData(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        if (file_exists(self::JSON_FILE)) {
            $json = file_get_contents(self::JSON_FILE);
            if ($json !== false) {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    // Ensure cached JSON includes full ashtakoota detail.
                    foreach ($decoded as &$row) {
                        if (isset($row['ettu']) && !isset($row['ashtakoota'])) {
                            $row['ashtakoota'] = self::ashtakootaFromEttu($row['ettu']);
                        }
                    }
                    unset($row);

                    self::$data = $decoded;
                    return self::$data;
                }
            }
        }

        // Fallback: parse CSV and cache it as JSON for faster subsequent reads.
        $rows = [];
        if (($handle = fopen(self::CSV_FILE, 'r')) !== false) {
            while (($line = fgetcsv($handle)) !== false) {
                if (count($line) < self::COL_SHREE + 1) {
                    continue;
                }

                $boy_star = intval($line[self::COL_BOY_STAR]);
                $boy_pad  = intval($line[self::COL_BOY_PAD]);
                $girl_star = intval($line[self::COL_GIRL_STAR]);
                $girl_pad  = intval($line[self::COL_GIRL_PAD]);

                $row = [
                    'boy_nakshatra' => $boy_star,
                    'boy_paadham' => $boy_pad,
                    'girl_nakshatra' => $girl_star,
                    'girl_paadham' => $girl_pad,
                    'score' => floatval($line[self::COL_SCORE]),
                    'ashtakoota' => self::ashtakootaFromLine($line),
                    'naalu' => [
                        'mahendra' => self::toBool($line[self::COL_MAHENDRA]),
                        'vedha' => self::toBool($line[self::COL_VEDHA]),
                        'rajju' => self::toBool($line[self::COL_RAJJU]),
                        'shreedheerga' => self::toBool($line[self::COL_SHREE]),
                    ],
                ];

                $rows[] = $row;
            }
            fclose($handle);
        }

        // cache as JSON (best-effort)
        @file_put_contents(self::JSON_FILE, json_encode($rows));
        self::$data = $rows;

        return self::$data;
    }

    private static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $v = strtolower(trim((string)$value));
        return in_array($v, ['1', 'true', 't', 'yes', 'y'], true);
    }

    private static function ashtakootaFromLine(array $line): array
    {
        $ettu = [
            'varna' => floatval($line[self::COL_VARNA]),
            'vashya' => floatval($line[self::COL_VASIYA]),
            'gana' => floatval($line[self::COL_GANA]),
            'tara' => floatval($line[self::COL_STAR]),
            'yoni' => floatval($line[self::COL_YONI]),
            'adhipathi' => floatval($line[self::COL_ADHIPATHI]),
            'rasi' => floatval($line[self::COL_RASI]),
            'nadi' => floatval($line[self::COL_NADI]),
        ];

        return self::ashtakootaFromEttu($ettu);
    }

    private static function ashtakootaFromEttu(array $ettu): array
    {
        $total = array_sum($ettu);
        $max = 36.0;
        $pass = $total >= 18.0;

        return [
            'values' => $ettu,
            'total' => $total,
            'max' => $max,
            'percent' => $max > 0 ? ($total / $max) * 100 : 0,
            'passed' => $pass,
        ];
    }

    /**
     * Match two charts based on the CSV compatibility table.
     *
     * @param int|null $boyNak
     * @param int|null $boyPad
     * @param int|null $girlNak
     * @param int|null $girlPad
     * @param float|null $minScore
     * @param array $flags boolean filters: mahendra, vedha, rajju, shreedheerga
     * @return array
     */
    public static function match(
        ?int $boyNak,
        ?int $boyPad,
        ?int $girlNak,
        ?int $girlPad,
        ?float $minScore = null,
        array $flags = [],
        string $mode = 'rules'
    ): array {
        $data = self::loadData();

        $useLegacy = in_array(strtolower($mode), ['csv', 'legacy'], true);

        $results = [];
        foreach ($data as $row) {
            $bn = (int)$row['boy_nakshatra'];
            $bp = (int)$row['boy_paadham'];
            $gn = (int)$row['girl_nakshatra'];
            $gp = (int)$row['girl_paadham'];

            $ashtakoota = null;
            if ($useLegacy) {
                $ashtakoota = $row['ashtakoota'] ?? self::ashtakootaFromEttu($row['ettu'] ?? []);
            } else {
                try {
                    $ashtakoota = Ashtakoota::calculate($bn, $bp, $gn, $gp);
                } catch (\Throwable $e) {
                    // Fallback to legacy calculation if rule engine fails
                    $ashtakoota = $row['ashtakoota'] ?? self::ashtakootaFromEttu($row['ettu'] ?? []);
                }
            }

            $score = $useLegacy ? (float)$row['score'] : (float)($ashtakoota['total'] ?? 0);

            if ($minScore !== null && $score < $minScore) {
                continue;
            }

            $cond = true;
            if ($boyNak !== null) {
                $cond = $cond && ($bn === $boyNak);
                if ($boyPad !== null) {
                    $cond = $cond && ($bp === $boyPad);
                }
            }
            if ($girlNak !== null) {
                $cond = $cond && ($gn === $girlNak);
                if ($girlPad !== null) {
                    $cond = $cond && ($gp === $girlPad);
                }
            }

            if ($cond && !empty($flags)) {
                foreach (['mahendra', 'vedha', 'rajju', 'shreedheerga'] as $flag) {
                    if (!empty($flags[$flag]) && empty($row['naalu'][$flag])) {
                        $cond = false;
                        break;
                    }
                }
            }

            if (!$cond) {
                continue;
            }

            $partnerNak = $boyNak !== null ? $gn : $bn;
            $partnerPad = $boyPad !== null ? $gp : $bp;

            // If no partner set (both null) return a full row.
            $results[] = [
                'boy_nakshatra' => $bn,
                'boy_paadham' => $bp,
                'girl_nakshatra' => $gn,
                'girl_paadham' => $gp,
                'partner_nakshatra' => $partnerNak,
                'partner_paadham' => $partnerPad,
                'score' => $score,
                'ettu' => $row['ettu'],
                'ashtakoota' => $ashtakoota,
                'naalu' => $row['naalu'],
            ];
        }

        return $results;
    }
}
