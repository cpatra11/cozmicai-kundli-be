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
     * Key of Kaala (Sun's portion)
     */
    const KEY_KAALA = 'Kaala';
    /**
     * Key of Mrityu (Mars's portion)
     */
    const KEY_MRITYU = 'Mrityu';
    /**
     * Key of Artha Praharaka (Mercury's portion)
     */
    const KEY_ARTHA_PRAHARAKA = 'ArthaPraharaka';
    /**
     * Key of Yamaghantaka (Jupiter's portion)
     */
    const KEY_YAMAGHANTAKA = 'YamaGhantaka';
    /**
     * Key of Gulika (Saturn's portion start)
     */
    const KEY_GULIKA = 'Gulika';
    /**
     * Key of Mandi (Saturn's portion end)
     */
    const KEY_MANDI = 'Mandi';
    
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
        self::KEY_KAALA => 'Kaala',
        self::KEY_MRITYU => 'Mrityu',
        self::KEY_ARTHA_PRAHARAKA => 'Artha Praharaka',
        self::KEY_YAMAGHANTAKA => 'Yama Ghantaka',
        self::KEY_GULIKA => 'Gulika',
        self::KEY_MANDI => 'Mandi',
    ];
    
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
     * Common calc helper and ensures rising data is present.
     *
     * @return array
     */
    protected function getDataWithRising()
    {
        $data = $this->getData();
        if (!isset($data['rising'])) {
            $this->getDataInstance()->calcPanchanga();
            $this->getDataInstance()->calcRising();
            $data = $this->getData();
        }
        return $data;
    }

    /**
     * Get time-based upagraha longitudes.
     *
     * @return array
     */
    protected function getTimeBasedUpagrahas()
    {
        if (!isset($this->temp['timeBased'])) {
            $this->temp['timeBased'] = $this->calculateTimeBasedUpagrahas();
        }
        return $this->temp['timeBased'];
    }

    /**
     * Calculate all time-based upagraha longitudes.
     *
     * @return array
     */
    protected function calculateTimeBasedUpagrahas()
    {
        $data = $this->getDataWithRising();
        $dateTime = $this->getDataInstance()->getDateTime();
        $timeZone = $dateTime->getTimezone();

        $risingData = $data['rising'][Graha::KEY_SY] ?? [];
        if (empty($risingData) || count($risingData) < 2) {
            throw new \Exception('Unable to calculate time-based upagrahas: insufficient rising data');
        }

        $risingToday = new \DateTime($risingData[1]['rising'], $timeZone);
        $settingToday = new \DateTime($risingData[1]['setting'], $timeZone);
        $riseTomorrow = isset($risingData[2]) ? new \DateTime($risingData[2]['rising'], $timeZone) : null;
        $settingYesterday = isset($risingData[0]) ? new \DateTime($risingData[0]['setting'], $timeZone) : null;

        $isDayBirth = $dateTime > $risingToday && $dateTime < $settingToday;

        if ($isDayBirth) {
            $start = $risingToday;
            $end = $settingToday;
        } elseif ($dateTime > $settingToday && $riseTomorrow !== null) {
            $start = $settingToday;
            $end = $riseTomorrow;
        } elseif ($settingYesterday !== null && $dateTime < $risingToday) {
            $start = $settingYesterday;
            $end = $risingToday;
        } else {
            // fallback: treat as day
            $start = $risingToday;
            $end = $settingToday;
            $isDayBirth = true;
        }

        $duration = ($end->getTimestamp() - $start->getTimestamp()) / 60.0;
        if ($duration <= 0) {
            $duration = 24 * 60;
        }

        $weekday = (int) $dateTime->format('w'); // 0=Sunday
        $lords = $isDayBirth ? $this->getPraharalords($weekday, 'day') : $this->getPraharalords($weekday, 'night');
        $praharaLength = $duration / 8.0;

        $ascendantLong = $data['lagna'][Lagna::KEY_LG]['longitude'] ?? 0;

        $results = [];

        $upagrahaLords = [
            self::KEY_KAALA => Graha::KEY_SY,
            self::KEY_MRITYU => Graha::KEY_MA,
            self::KEY_ARTHA_PRAHARAKA => Graha::KEY_BU,
            self::KEY_YAMAGHANTAKA => Graha::KEY_GU,
        ];

        foreach ($upagrahaLords as $upagrahaKey => $lordKey) {
            $praharaIndex = array_search($lordKey, $lords, true);
            if ($praharaIndex === false) {
                continue;
            }
            $results[$upagrahaKey] = $this->longitudeAtTime($praharaIndex * $praharaLength, $ascendantLong, $duration);
        }

        $saturnIndex = array_search(Graha::KEY_SA, $lords, true);
        if ($saturnIndex !== false) {
            $saturnStart = $saturnIndex * $praharaLength;
            $results[self::KEY_GULIKA] = $this->longitudeAtTime($saturnStart, $ascendantLong, $duration);
            $results[self::KEY_MANDI] = $this->longitudeAtTime($saturnStart + $praharaLength, $ascendantLong, $duration);
        }

        return $results;
    }

    /**
     * Get prahara lords for day or night.
     *
     * @param int $weekday
     * @param string $period "day" or "night"
     * @return array
     */
    protected function getPraharalords($weekday, $period)
    {
        $baseOrder = [
            Graha::KEY_SY,
            Graha::KEY_CH,
            Graha::KEY_MA,
            Graha::KEY_BU,
            Graha::KEY_GU,
            Graha::KEY_SK,
            Graha::KEY_SA,
        ];

        $weekday = $weekday % 7;
        $rotated = array_merge(array_slice($baseOrder, $weekday), array_slice($baseOrder, 0, $weekday));

        if ($period === 'night') {
            return array_merge(array_slice($rotated, 4), array_slice($rotated, 0, 4));
        }

        return $rotated;
    }

    /**
     * Convert a time offset within day/night to longitude.
     *
     * @param float $minutesFromStart
     * @param float $ascendantLong
     * @param float $durationMinutes
     * @return float
     */
    protected function longitudeAtTime($minutesFromStart, $ascendantLong, $durationMinutes)
    {
        if ($durationMinutes <= 0) {
            return $ascendantLong;
        }
        $fraction = $minutesFromStart / $durationMinutes;
        $longitude = $ascendantLong + ($fraction * 360.0);

        while ($longitude >= 360.0) {
            $longitude -= 360.0;
        }
        while ($longitude < 0.0) {
            $longitude += 360.0;
        }

        return $longitude;
    }

    /**
     * Get Kaala (time-based) upagraha.
     *
     * @return array
     */
    public function getKaala()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_KAALA])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_KAALA] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_KAALA] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_KAALA];
    }

    /**
     * Get Mrityu (time-based) upagraha.
     *
     * @return array
     */
    public function getMrityu()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_MRITYU])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_MRITYU] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_MRITYU] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_MRITYU];
    }

    /**
     * Get Artha Praharaka (time-based) upagraha.
     *
     * @return array
     */
    public function getArthaPraharaka()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_ARTHA_PRAHARAKA])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_ARTHA_PRAHARAKA] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_ARTHA_PRAHARAKA] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_ARTHA_PRAHARAKA];
    }

    /**
     * Get Yama Ghantaka (time-based) upagraha.
     *
     * @return array
     */
    public function getYamaGhantaka()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_YAMAGHANTAKA])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_YAMAGHANTAKA] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_YAMAGHANTAKA] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_YAMAGHANTAKA];
    }

    /**
     * Get Gulika (time-based) upagraha.
     *
     * @return array
     */
    public function getGulika()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_GULIKA])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_GULIKA] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_GULIKA] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_GULIKA];
    }

    /**
     * Get Mandi (time-based) upagraha.
     *
     * @return array
     */
    public function getMandi()
    {
        $this->checkData();

        if (!isset($this->temp[self::KEY_MANDI])) {
            $timeBased = $this->getTimeBasedUpagrahas();
            $lng = $timeBased[self::KEY_MANDI] ?? 0;
            $unit = Math::partsToUnits($lng);
            $this->temp[self::KEY_MANDI] = ['longitude' => $lng, 'rashi' => $unit['units'], 'degree' => $unit['parts']];
        }

        return $this->temp[self::KEY_MANDI];
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
