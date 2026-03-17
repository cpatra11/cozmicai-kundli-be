<?php

namespace App\Lib;

class Ashtakoota
{
    private const NAKSHATRA_FILE = __DIR__ . '/../../data/nakshatras.json';

    /** @var array<int,array>|null */
    private static $nakshatras = null;

    /** @var array<string,array>|null */
    private static $compatData = null;
    /** @var array<string,array>|null */
    private static $compatIndex = null;

    /**
     * Load Nakshatra master data from JSON.
     *
     * @return array<int,array>
     */
    private static function loadNakshatras(): array
    {
        if (self::$nakshatras !== null) {
            return self::$nakshatras;
        }

        if (!file_exists(self::NAKSHATRA_FILE)) {
            throw new \RuntimeException('Nakshatra master data not found: ' . self::NAKSHATRA_FILE);
        }

        $json = file_get_contents(self::NAKSHATRA_FILE);
        if ($json === false) {
            throw new \RuntimeException('Failed to read nakshatra master data');
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid nakshatra JSON data');
        }

        $byId = [];
        foreach ($data as $row) {
            if (!isset($row['id']) || !is_numeric($row['id'])) {
                continue;
            }
            $byId[(int)$row['id']] = $row;
        }

        self::$nakshatras = $byId;
        return self::$nakshatras;
    }

    public static function getNakshatra(int $id): ?array
    {
        $nak = self::loadNakshatras();
        return $nak[$id] ?? null;
    }

    public static function getRashiFromNakshatraAndPada(int $nak, int $pad): int
    {
        // 27 nakshatras * 4 padas = 108 padas, 12 rashis => 9 padas per rashi
        $nak = max(1, min(27, $nak));
        $pad = max(1, min(4, $pad));
        $index = ($nak - 1) * 4 + ($pad - 1); // 0..107
        return intdiv($index, 9) + 1; // 1..12
    }

    private static function loadCompatibilityData(): array
    {
        if (self::$compatData !== null) {
            return self::$compatData;
        }

        $file = __DIR__ . '/../../data/all_nak_pad_boy_girl.json';
        if (!file_exists($file)) {
            throw new \RuntimeException('Compatibility data not found: ' . $file);
        }

        $json = file_get_contents($file);
        if ($json === false) {
            throw new \RuntimeException('Failed to read compatibility data');
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid compatibility JSON data');
        }

        self::$compatData = $decoded;
        self::$compatIndex = [];
        foreach ($decoded as $row) {
            $key = sprintf('%d:%d:%d:%d', $row['boy_nakshatra'], $row['boy_paadham'], $row['girl_nakshatra'], $row['girl_paadham']);
            self::$compatIndex[$key] = $row;
        }

        return self::$compatData;
    }

    private static function findCompatibilityRow(int $boyNak, int $boyPad, int $girlNak, int $girlPad): ?array
    {
        if (self::$compatIndex === null) {
            self::loadCompatibilityData();
        }

        $key = sprintf('%d:%d:%d:%d', $boyNak, $boyPad, $girlNak, $girlPad);
        return self::$compatIndex[$key] ?? null;
    }

    /**
     * Calculate Ashtakoota values + reasons.
     *
     * Uses the legacy CSV dataset for the score values (parity), but provides
     * human-readable reasoning based on the underlying nakshatra attributes.
     */
    public static function calculate(int $boyNak, int $boyPad, int $girlNak, int $girlPad): array
    {
        $boy = self::getNakshatra($boyNak);
        $girl = self::getNakshatra($girlNak);

        if (!$boy || !$girl) {
            throw new \InvalidArgumentException('Invalid nakshatra id');
        }

        $row = self::findCompatibilityRow($boyNak, $boyPad, $girlNak, $girlPad);

        if (!$row) {
            // Fall back to computed rules if dataset doesn't include this combination
            $row = ['ettu' => [], 'score' => 0];
        }

        $values = [
            'varna' => $row['ettu'][0] ?? 0,
            'vashya' => $row['ettu'][1] ?? 0,
            'gana' => $row['ettu'][2] ?? 0,
            'tara' => $row['ettu'][3] ?? 0,
            'yoni' => $row['ettu'][4] ?? 0,
            'adhipathi' => $row['ettu'][5] ?? 0,
            'rasi' => $row['ettu'][6] ?? 0,
            'nadi' => $row['ettu'][7] ?? 0,
        ];

        $reasons = [
            'varna' => sprintf('Boy varna %s, Girl varna %s', $boy['varna'], $girl['varna']),
            'vashya' => sprintf('Boy vashya %s, Girl vashya %s', $boy['vashya'], $girl['vashya']),
            'gana' => sprintf('Boy gana %s, Girl gana %s', $boy['gana'], $girl['gana']),
            'tara' => sprintf('Tara difference between Nak %d and %d', $boyNak, $girlNak),
            'yoni' => sprintf('Boy yoni %s, Girl yoni %s', $boy['yoni'], $girl['yoni']),
            'adhipathi' => sprintf('Boy lord %s, Girl lord %s', $boy['lord'], $girl['lord']),
            'rasi' => sprintf('Boy rashi %d, Girl rashi %d', self::getRashiFromNakshatraAndPada($boyNak, $boyPad), self::getRashiFromNakshatraAndPada($girlNak, $girlPad)),
            'nadi' => sprintf('Boy nadi %s, Girl nadi %s', $boy['nadi'], $girl['nadi']),
        ];

        $total = array_sum($values);
        $max = 36.0;
        $pass = $total >= 18.0;

        return [
            'values' => $values,
            'reasons' => $reasons,
            'total' => $total,
            'max' => $max,
            'percent' => $max > 0 ? ($total / $max) * 100 : 0,
            'passed' => $pass,
        ];
    }

    private static function calcVarnaPoints(array $boy, array $girl): array
    {
        $order = ['Brahmin' => 0, 'Kshatriya' => 1, 'Vaishya' => 2, 'Shudra' => 3];
        $b = $boy['varna'] ?? null;
        $g = $girl['varna'] ?? null;

        if (!isset($order[$b]) || !isset($order[$g])) {
            return [0, 'Unknown varna'];
        }

        if ($b === $g) {
            return [2, "Both are $b (same)"];
        }

        $diff = abs($order[$b] - $order[$g]);
        if ($diff === 1) {
            return [1, "Adjacent varna ($b/$g) - partially compatible"];
        }

        return [0, "Distant varna ($b/$g) - incompatible"];
    }

    private static function calcVashyaPoints(array $boy, array $girl): array
    {
        $b = $boy['vashya'] ?? null;
        $g = $girl['vashya'] ?? null;
        if (!$b || !$g) {
            return [0, 'Unknown vashya'];
        }

        if ($b === $g) {
            return [2, "Same vashya ($b) - full compatibility"];
        }

        $pairs = [
            ['Manav', 'Vanchar'],
            ['Chatushpada', 'Jalachara'],
        ];

        foreach ($pairs as $pair) {
            if ((($b === $pair[0]) && ($g === $pair[1])) || (($b === $pair[1]) && ($g === $pair[0]))) {
                return [1, "Complementary vashya ($b/$g) - partial compatibility"];
            }
        }

        return [0, "Incompatible vashya ($b/$g)"];
    }

    private static function calcTaraPoints(int $boyNak, int $girlNak): array
    {
        $diff = ($girlNak - $boyNak + 27) % 27;
        $mod9 = $diff % 9;

        // Favorable: Sampat (1), Parama Mitra (2), Kshema (4), Sadhak (5), Mitra (7)
        // Unfavorable: Janma (0), Vipat (3), Pratyak (6), Vadha (8)
        $friendly = [1, 2, 4, 5, 7];
        $typeMap = [
            0 => 'Janma',
            1 => 'Sampat',
            2 => 'Parama Mitra',
            3 => 'Vipat',
            4 => 'Kshema',
            5 => 'Sadhak',
            6 => 'Pratyak',
            7 => 'Mitra',
            8 => 'Vadha',
        ];
        $type = $typeMap[$mod9] ?? 'Unknown';
        $points = in_array($mod9, $friendly, true) ? 3 : 0;

        $reason = sprintf('Tara type %s (%d) - %s', $type, $diff, $points > 0 ? 'favorable' : 'unfavorable');
        return [$points, $reason];
    }

    private static function calcYoniPoints(array $boy, array $girl): array
    {
        $b = $boy['yoni'] ?? null;
        $g = $girl['yoni'] ?? null;
        if (!$b || !$g) {
            return [0, 'Unknown yoni'];
        }

        if ($b === $g) {
            return [4, "Same yoni ($b) - strongest compatibility"];
        }

        $friendlyPairs = [
            ['Horse', 'Elephant'],
            ['Sheep', 'Cow'],
            ['Dog', 'Cat'],
            ['Rat', 'Buffalo'],
            ['Tiger', 'Monkey'],
            ['Deer', 'Mongoose'],
            ['Lion', 'Serpent'],
        ];

        foreach ($friendlyPairs as $pair) {
            if ((($b === $pair[0]) && ($g === $pair[1])) || (($b === $pair[1]) && ($g === $pair[0]))) {
                return [3, "Friendly yoni pair ($b / $g)"];
            }
        }

        $enemyPairs = [
            ['Cat', 'Rat'],
            ['Cow', 'Buffalo'],
            ['Dog', 'Sheep'],
            ['Horse', 'Monkey'],
            ['Tiger', 'Deer'],
            ['Lion', 'Mongoose'],
        ];

        foreach ($enemyPairs as $pair) {
            if ((($b === $pair[0]) && ($g === $pair[1])) || (($b === $pair[1]) && ($g === $pair[0]))) {
                return [1, "Enemy yoni pair ($b / $g)"];
            }
        }

        return [2, "Neutral yoni relationship ($b / $g)"];
    }

    private static function calcAdhipathiPoints(array $boy, array $girl): array
    {
        $lordA = $boy['lord'] ?? null;
        $lordB = $girl['lord'] ?? null;
        if (!$lordA || !$lordB) {
            return [0, 'Unknown planetary lords'];
        }

        if ($lordA === $lordB) {
            return [5, "Same lord ($lordA) - best friendship"];
        }

        $friendship = self::planetFriendshipMap();
        if (isset($friendship[$lordA][$lordB])) {
            return [$friendship[$lordA][$lordB], "Planetary friendship: $lordA vs $lordB"];
        }

        // Symmetric fallback
        if (isset($friendship[$lordB][$lordA])) {
            return [$friendship[$lordB][$lordA], "Planetary friendship: $lordA vs $lordB"];
        }

        return [3, "Neutral planetary friendship: $lordA vs $lordB"];
    }

    /**
     * Planetary friendship scoring (1-5)
     *
     * @return array<string,array<string,int>>
     */
    private static function planetFriendshipMap(): array
    {
        // Based on common Jyotish friendship tables.
        // 5 = best friends, 4 = friends, 3 = neutral, 2 = enemy, 1 = bitter enemy.
        return [
            'Sun' => ['Moon' => 5, 'Mars' => 5, 'Jupiter' => 5, 'Venus' => 1, 'Saturn' => 2, 'Mercury' => 3, 'Rahu' => 2, 'Ketu' => 2],
            'Moon' => ['Sun' => 5, 'Mercury' => 5, 'Mars' => 3, 'Jupiter' => 4, 'Venus' => 2, 'Saturn' => 2, 'Rahu' => 2, 'Ketu' => 2],
            'Mars' => ['Sun' => 5, 'Moon' => 3, 'Jupiter' => 5, 'Mercury' => 1, 'Venus' => 2, 'Saturn' => 2, 'Rahu' => 2, 'Ketu' => 2],
            'Mercury' => ['Sun' => 3, 'Moon' => 5, 'Venus' => 5, 'Mars' => 1, 'Jupiter' => 3, 'Saturn' => 4, 'Rahu' => 3, 'Ketu' => 3],
            'Jupiter' => ['Sun' => 5, 'Moon' => 4, 'Mars' => 5, 'Mercury' => 3, 'Venus' => 2, 'Saturn' => 3, 'Rahu' => 2, 'Ketu' => 2],
            'Venus' => ['Sun' => 1, 'Moon' => 2, 'Mars' => 2, 'Mercury' => 5, 'Jupiter' => 2, 'Saturn' => 5, 'Rahu' => 3, 'Ketu' => 3],
            'Saturn' => ['Sun' => 2, 'Moon' => 2, 'Mars' => 2, 'Mercury' => 4, 'Jupiter' => 3, 'Venus' => 5, 'Rahu' => 3, 'Ketu' => 3],
            'Rahu' => ['Sun' => 2, 'Moon' => 2, 'Mars' => 2, 'Mercury' => 3, 'Jupiter' => 2, 'Venus' => 3, 'Saturn' => 3, 'Ketu' => 5],
            'Ketu' => ['Sun' => 2, 'Moon' => 2, 'Mars' => 2, 'Mercury' => 3, 'Jupiter' => 2, 'Venus' => 3, 'Saturn' => 3, 'Rahu' => 5],
        ];
    }

    private static function calcGanaPoints(array $boy, array $girl): array
    {
        $b = $boy['gana'] ?? null;
        $g = $girl['gana'] ?? null;
        if (!$b || !$g) {
            return [0, 'Unknown gana'];
        }

        if ($b === $g) {
            return [6, "Same gana ($b) - full compatibility"];
        }

        $order = ['Deva' => 0, 'Manushya' => 1, 'Rakshasa' => 2];
        if (!isset($order[$b]) || !isset($order[$g])) {
            return [0, "Unknown gana values ($b/$g)"];
        }

        $diff = abs($order[$b] - $order[$g]);
        if ($diff === 1) {
            if ($b === 'Deva' || $g === 'Deva') {
                return [5, "Deva-Manushya pairing ($b/$g) - high compatibility"];
            }
            return [4, "Manushya-Rakshasa pairing ($b/$g) - moderate compatibility"];
        }

        return [0, "Deva-Rakshasa pairing ($b/$g) - low compatibility"];
    }

    private static function calcRasiPoints(int $boyNak, int $boyPad, int $girlNak, int $girlPad): array
    {
        $br = self::getRashiFromNakshatraAndPada($boyNak, $boyPad);
        $gr = self::getRashiFromNakshatraAndPada($girlNak, $girlPad);

        // Standard Bhakoot based on rashi positions
        // Use difference (from boy->girl) and map to score
        $diff = ($gr - $br + 12) % 12;

        $scoreMap = [
            0 => 0, // same rashi
            1 => 7,
            2 => 0,
            3 => 7,
            4 => 5,
            5 => 0,
            6 => 7,
            7 => 0,
            8 => 6,
            9 => 5,
            10 => 7,
            11 => 0,
        ];

        $score = $scoreMap[$diff] ?? 0;
        $reason = sprintf('Bhakoot: %d->%d (%d) => %d', $br, $gr, $diff, $score);

        return [$score, $reason];
    }

    private static function calcNadiPoints(array $boy, array $girl): array
    {
        $b = $boy['nadi'] ?? null;
        $g = $girl['nadi'] ?? null;
        if (!$b || !$g) {
            return [0, 'Unknown nadi'];
        }

        if ($b === $g) {
            return [0, "Same nadi ($b) - not recommended"];
        }

        return [8, "Different nadi ($b / $g) - good compatibility"];
    }
}

