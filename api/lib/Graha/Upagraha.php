<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Graha;

use Jyotish\Graha\Graha;
use Jyotish\Ganita\Math;

/**
 * Upagraha calculation class.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
class Upagraha
{
    use \Jyotish\Base\Traits\DataTrait;
    
    /**
     * Key of Dhooma
     */
    const KEY_DH = 'Dh';
    /**
     * Key of Vyatipata
     */
    const KEY_VY = 'Vy';
    /**
     * Key of Parivesha
     */
    const KEY_PA = 'Pa';
    /**
     * Key of Indrachapa
     */
    const KEY_IN = 'In';
    /**
     * Key of Upaketu
     */
    const KEY_UK = 'Uk';

    /**
     * Key of Kaala
     */
    const KEY_KA = 'Ka';
    /**
     * Key of Mrityu
     */
    const KEY_MR = 'Mr';
    /**
     * Key of Ardhaprahara
     */
    const KEY_AP = 'Ap';
    /**
     * Key of Yamaghantaka
     */
    const KEY_YA = 'Ya';
    /**
     * Key of Gulika
     */
    const KEY_GU = 'Gu';
    /**
     * Key of Mandi
     */
    const KEY_MA = 'Ma';
    
    /**
     * List of Upagrahas.
     * 
     * @var array
     */
    public static $upagraha = [
        self::KEY_DH => 'Dhooma',
        self::KEY_VY => 'Vyatipata',
        self::KEY_PA => 'Parivesha',
        self::KEY_IN => 'Indrachapa',
        self::KEY_UK => 'Upaketu',
        self::KEY_KA => 'Kaala',
        self::KEY_MR => 'Mrityu',
        self::KEY_AP => 'Ardhaprahara',
        self::KEY_YA => 'Yamaghantaka',
        self::KEY_GU => 'Gulika',
        self::KEY_MA => 'Mandi',
    ];
    
    /**
     * Use exact ascendant calculation for time-based upagrahas.
     *
     * When true, the ascendant is recalculated at the prahara time (as other apps do).
     * When false, longitude is derived from Ascendant + (t/duration)*360 (as in upgraha_2.md).
     *
     * @var bool
     */
    protected $optionUseExactAscendant = true;

    /**
     * Constructor
     * 
     * @param \Jyotish\Base\Data $Data
     */
    public function __construct(\Jyotish\Base\Data $Data)
    {
        $this->setDataInstance($Data);
    }
    
    /**
     * Dhooma calculation.
     * 
     * @return array
     * @see Mantreswara. Phaladeepika. Chapter 25, Verse 5.
     */
    public function getDh()
    {
        $this->checkData();
            
        if (!isset($this->temp[self::KEY_DH])) {
            $result = $this->getData()['graha'][Graha::KEY_SY]['longitude'] + 133 + 1/3;
            $lng = $result > 360 ? $result - 360 : $result;
            $unit = Math::partsToUnits($lng);

            $this->temp[self::KEY_DH] = [
                'longitude' => $lng,
                'rashi' => $unit['units'],
                'degree' => $unit['parts']
            ];
        }
        return $this->temp[self::KEY_DH];
    }
    
    /**
     * Vyatipata calculation.
     * 
     * @return array
     * @see Mantreswara. Phaladeepika. Chapter 25, Verse 5.
     */
    public function getVy()
    {
        if (!isset($this->temp[self::KEY_VY])) {
            $lng = 360 - $this->getDh()['longitude'];
            $unit = Math::partsToUnits($lng);
            
            $this->temp[self::KEY_VY] = [
                'longitude' => $lng,
                'rashi' => $unit['units'],
                'degree' => $unit['parts']
            ];
        }
        return $this->temp[self::KEY_VY];
    }
    
    /**
     * Parivesha calculation.
     * 
     * @return array
     * @see Mantreswara. Phaladeepika. Chapter 25, Verse 5.
     */
    public function getPa()
    {
        if (!isset($this->temp[self::KEY_PA])) {
            $result = $this->getVy()['longitude'] + 180;
            $lng = $result > 360 ? $result - 360 : $result;
            $unit = Math::partsToUnits($lng);
            
            $this->temp[self::KEY_PA] = [
                'longitude' => $lng,
                'rashi' => $unit['units'],
                'degree' => $unit['parts']
            ];
        }
        return $this->temp[self::KEY_PA];
    }
    
    /**
     * Indrachapa calculation.
     * 
     * @return array
     * @see Mantreswara. Phaladeepika. Chapter 25, Verse 5.
     */
    public function getIn()
    {
        if (!isset($this->temp[self::KEY_IN])) {
            $lng = 360 - $this->getPa()['longitude'];
            $unit = Math::partsToUnits($lng);
            
            $this->temp[self::KEY_IN] = [
                'longitude' => $lng,
                'rashi' => $unit['units'],
                'degree' => $unit['parts']
            ];
        }
        return $this->temp[self::KEY_IN];
    }
    
    /**
     * Upaketu calculation.
     * 
     * @return array
     * @see Mantreswara. Phaladeepika. Chapter 25, Verse 5.
     */
    public function getUk()
    {
        if (!isset($this->temp[self::KEY_UK])) {
            $result = $this->getIn()['longitude'] + 16 + 2/3;
            $lng = $result > 360 ? $result - 360 : $result;
            $unit = Math::partsToUnits($lng);

            $this->temp[self::KEY_UK] = [
                'longitude' => $lng,
                'rashi' => $unit['units'],
                'degree' => $unit['parts']
            ];
        }
        return $this->temp[self::KEY_UK];
    }

    /**
     * Check data.
     * 
     * @param null|string $function Function name
     * @return void
     */
    private function checkData($function = null)
    {
        if (!isset($this->getData()['graha'])) {
            $this->Data->calcParams();
        }
        if (!isset($this->getData()['rising'])) {
            $this->Data->calcRising();
        }
    }

    /**
     * Normalize longitude to 0-360 range.
     *
     * @param float $longitude
     * @return float
     */
    protected function normalizeLongitude(float $longitude): float
    {
        while ($longitude >= 360) {
            $longitude -= 360;
        }
        while ($longitude < 0) {
            $longitude += 360;
        }
        return $longitude;
    }

    /**
     * Parse a date/time string into fractional hours.
     *
     * @param string $dateTimeString
     * @param \DateTimeZone $tz
     * @return float
     */
    protected function timeStringToHours(string $dateTimeString, \DateTimeZone $tz): float
    {
        $dt = new \DateTime($dateTimeString, $tz);
        return (float) $dt->format('H') + (float) $dt->format('i') / 60 + (float) $dt->format('s') / 3600;
    }

    /**
     * Calculate a time-based upagraha longitude based on day/night prahara divisions.
     *
     * @param string $lordKey Graha key (e.g. 'Su', 'Ma')
     * @param string $mode   'start'|'middle'|'end'
     * @return array|null
     */
    protected function calculateTimeBasedUpagraha(string $lordKey, string $mode = 'start')
    {
        $data = $this->getData();
        $rising = $data['rising'][Graha::KEY_SY] ?? null;
        if (!is_array($rising) || count($rising) < 3) {
            return null;
        }

        $dateTime = $this->Data->getDateTime();
        $tz = $dateTime->getTimezone();

        // Map graha key to index used by prahara rulers (Sun=0, Moon=1, Mars=2, Mercury=3, Jupiter=4, Venus=5, Saturn=6)
        $planetIndexMap = [
            Graha::KEY_SY => 0,
            Graha::KEY_CH => 1,
            Graha::KEY_MA => 2,
            Graha::KEY_BU => 3,
            Graha::KEY_GU => 4,
            Graha::KEY_SK => 5,
            Graha::KEY_SA => 6,
        ];
        $planetIndex = $planetIndexMap[$lordKey] ?? 0;

        // Day/night bounds (in fractional hours)
        $sunrise = $this->timeStringToHours($rising[1]['rising'], $tz);
        $sunset = $this->timeStringToHours($rising[1]['setting'], $tz);
        $prevSunset = $this->timeStringToHours($rising[0]['setting'], $tz);
        $nextSunrise = $this->timeStringToHours($rising[2]['rising'], $tz);

        $birthHrs = (float) $dateTime->format('H') + (float) $dateTime->format('i') / 60 + (float) $dateTime->format('s') / 3600;
        $weekday = (int) $dateTime->format('w'); // 0=Sun..6=Sat

        // Determine whether birth is during day or night and set the prahara period.
        if ($birthHrs >= $sunrise && $birthHrs < $sunset) {
            $periodStart = $sunrise;
            $periodEnd = $sunset;
            $praharaLords = $this->getDayPraharas($weekday);
        } elseif ($birthHrs < $sunrise) {
            // Night of previous day
            $periodStart = $prevSunset;
            $periodEnd = $sunrise + 24; // treat as next-day sunrise
            $weekday = ($weekday + 6) % 7; // previous weekday for night sequence
            $praharaLords = $this->getNightPraharas($weekday);
        } else {
            // Night after sunset
            $periodStart = $sunset;
            $periodEnd = $nextSunrise + 24;
            $praharaLords = $this->getNightPraharas($weekday);
        }

        $durationMinutes = ($periodEnd - $periodStart) * 60;
        if ($durationMinutes <= 0) {
            return null;
        }

        $praharaLength = $durationMinutes / 8.0;

        // Determine which prahara index corresponds to the requested lord.
        if ($lordKey === Graha::KEY_SA && ($mode === 'middle' || $mode === 'end')) {
            // For Mandi use the last Saturn prahara
            $matches = array_keys($praharaLords, $planetIndex, true);
            $prIndex = count($matches) ? max($matches) : false;
        } else {
            // For everything else use the first prahara where that lord appears
            $prIndex = array_search($planetIndex, $praharaLords, true);
        }
        if ($prIndex === false) {
            $prIndex = 0;
        }

        $timeOffset = $prIndex * $praharaLength;
        if ($mode === 'middle') {
            $timeOffset += $praharaLength / 2.0;
        } elseif ($mode === 'end') {
            $timeOffset += $praharaLength;
        }

        if ($this->optionUseExactAscendant) {
            // Compute the actual ascendant at the prahara moment (matches most apps).
            $periodStartDate = (clone $dateTime)->setTime(0, 0, 0);
            $secondsFromMidnight = ($periodStart * 3600) + ($timeOffset * 60);

            while ($secondsFromMidnight < 0) {
                $secondsFromMidnight += 86400;
                $periodStartDate->modify('-1 day');
            }
            while ($secondsFromMidnight >= 86400) {
                $secondsFromMidnight -= 86400;
                $periodStartDate->modify('+1 day');
            }

            $hours = floor($secondsFromMidnight / 3600);
            $minutes = floor(($secondsFromMidnight % 3600) / 60);
            $seconds = (int)($secondsFromMidnight % 60);

            $periodStartDate->setTime($hours, $minutes, $seconds);

            $dataAtTime = clone $this->Data;
            $dataAtTime->setDateTime($periodStartDate);
            $dataAtTime->calcParams();

            $longitude = $dataAtTime->getData()['lagna'][Graha::KEY_LG]['longitude'] ?? 0;
        } else {
            // Use the simplified linear Ascendant movement model (upgraha_2.md)
            $ascendantLng = $data['lagna'][Graha::KEY_LG]['longitude'] ?? 0;
            $longitude = $this->normalizeLongitude($ascendantLng + ($timeOffset / $durationMinutes) * 360);
        }

        $unit = Math::partsToUnits($longitude);
        $ascRashi = $data['lagna'][Graha::KEY_LG]['rashi'] ?? 1;
        $houseNumber = ((int) $unit['units'] - (int) $ascRashi + 12) % 12 + 1;

        return [
            'longitude' => $longitude,
            'rashi' => $unit['units'],
            'degree' => $unit['parts'],
            'house_number' => $houseNumber,
        ];
    }

    /**
     * Get the 8 prahara lords for daytime (sunrise->sunset) based on weekday.
     *
     * @param int $weekday 0=Sun..6=Sat
     * @return int[] length 8 array of planet indices
     */
    private function getDayPraharas(int $weekday): array
    {
        // Day prahara lord table from upgraha_2.md
        $day = [
            // Sun, Mon, Tue, Wed, Thu, Fri, Sat
            [0,5,3,1,6,4,2,0], // Sunday
            [1,6,4,2,0,5,3,1], // Monday
            [2,0,5,3,1,6,4,2], // Tuesday
            [3,1,6,4,2,0,5,3], // Wednesday
            [4,2,0,5,3,1,6,4], // Thursday
            [5,3,1,6,4,2,0,5], // Friday
            [6,4,2,0,5,3,1,6], // Saturday
        ];

        return $day[$weekday] ?? $day[0];
    }

    /**
     * Get the 8 prahara lords for nighttime (sunset->next sunrise) based on weekday.
     *
     * @param int $weekday 0=Sun..6=Sat
     * @return int[] length 8 array of planet indices
     */
    private function getNightPraharas(int $weekday): array
    {
        $dayPraharas = $this->getDayPraharas($weekday);
        // Night sequence begins from the 5th element of the day sequence (index 4)
        $night = [];
        for ($i = 0; $i < 8; $i++) {
            $night[] = $dayPraharas[($i + 4) % 8];
        }
        return $night;
    }

    /**
     * Kaala calculation.
     *
     * @return array|null
     */
    public function getKa()
    {
        if (!isset($this->temp[self::KEY_KA])) {
            $this->checkData();
            $this->temp[self::KEY_KA] = $this->calculateTimeBasedUpagraha(Graha::KEY_SY, 'start');
        }
        return $this->temp[self::KEY_KA];
    }

    /**
     * Mrityu calculation.
     *
     * @return array|null
     */
    public function getMr()
    {
        if (!isset($this->temp[self::KEY_MR])) {
            $this->checkData();
            $this->temp[self::KEY_MR] = $this->calculateTimeBasedUpagraha(Graha::KEY_MA, 'start');
        }
        return $this->temp[self::KEY_MR];
    }

    /**
     * Ardhaprahara calculation.
     *
     * @return array|null
     */
    public function getAp()
    {
        if (!isset($this->temp[self::KEY_AP])) {
            $this->checkData();
            $this->temp[self::KEY_AP] = $this->calculateTimeBasedUpagraha(Graha::KEY_BU, 'start');
        }
        return $this->temp[self::KEY_AP];
    }

    /**
     * Yamaghantaka calculation.
     *
     * @return array|null
     */
    public function getYa()
    {
        if (!isset($this->temp[self::KEY_YA])) {
            $this->checkData();
            $this->temp[self::KEY_YA] = $this->calculateTimeBasedUpagraha(Graha::KEY_GU, 'start');
        }
        return $this->temp[self::KEY_YA];
    }

    /**
     * Gulika calculation (start of Saturn portion).
     *
     * @return array|null
     */
    public function getGu()
    {
        if (!isset($this->temp[self::KEY_GU])) {
            $this->checkData();
            $this->temp[self::KEY_GU] = $this->calculateTimeBasedUpagraha(Graha::KEY_SA, 'start');
        }
        return $this->temp[self::KEY_GU];
    }

    /**
     * Mandi calculation (middle of Saturn portion).
     *
     * @return array|null
     */
    public function getMa()
    {
        if (!isset($this->temp[self::KEY_MA])) {
            $this->checkData();
            // Mandi is usually taken at the end of Saturn's prahara.
            $this->temp[self::KEY_MA] = $this->calculateTimeBasedUpagraha(Graha::KEY_SA, 'end');
        }
        return $this->temp[self::KEY_MA];
    }

    /**
     * Generation of Upagrahas.
     * 
     * @param null|array $upagrahaKeys Array of upagraha keys
     * @throws Exception\InvalidArgumentException
     */
    public function generateUpagraha(array $upagrahaKeys = null)
    {
        if (is_null($upagrahaKeys)) {
            $upagrahaKeys = array_keys(self::$upagraha);
        }
        
        foreach ($upagrahaKeys as $key) {
            if (!array_key_exists($key, self::$upagraha)) {
                throw new Exception\InvalidArgumentException("Upagraha with the key '$key' does not exist.");
            }
            
            $getUpagraha = 'get'.$key;
            yield $key => $this->$getUpagraha();
        }
    }
}
