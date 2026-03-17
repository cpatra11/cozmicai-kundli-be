<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Varga\Object;

use Jyotish\Varga\Varga;
use Jyotish\Ganita\Math;

/**
 * Abstract varga class.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
abstract class AbstractVarga
{
    use \Jyotish\Base\Traits\GetTrait;
    use \Jyotish\Base\Traits\DataTrait;
    
    /**
     * Key of the varga.
     * 
     * @var string
     */
    protected $vargaKey = null;

    /**
     * Names of the varga.
     * 
     * @var array
     */
    protected $vargaNames = [];

    /**
     * The number of parts.
     * 
     * @var int
     */
    protected $vargaAmsha = null;

    /**
     * Get varga rashi.
     * 
     * @param array $ganitaRashi
     * @return array
     */
    abstract protected function getVargaRashi(array $ganitaRashi);

    /**
     * Convert sign-degree into amsha unit index with a tiny epsilon guard.
     *
     * This prevents boundary jitter (e.g., 2.999999999999 vs 3.000000000001)
     * from moving a graha one varga segment forward unexpectedly.
     *
     * @param float|int|string $degree Degree inside sign (0 <= degree < 30)
     * @param float $amshaSize Size of one amsha segment
     * @return array{units:int,parts:float}
     */
    protected function getAmshaResult($degree, $amshaSize)
    {
        $deg = (float) $degree;

        // normalize into [0, 30)
        $deg = fmod($deg, 30.0);
        if ($deg < 0) {
            $deg += 30.0;
        }

        // epsilon to keep exact/near boundaries from drifting to next segment
        $epsilon = 1e-9;
        if ($deg >= $epsilon) {
            $deg -= $epsilon;
        }

        return Math::partsToUnits($deg, $amshaSize, 'floor');
    }

    /**
     * Get varga data.
     * 
     * @return array
     */
    public function getVargaData() {
        $this->checkData();
        $vargaData = [];

        if ($this->vargaKey == Varga::KEY_D1) {
            $vargaData = $this->getData(\Jyotish\Base\Data::listBlock('main'));

            $ascRashi = isset($vargaData['lagna']['Lg']['rashi'])
                ? (int) $vargaData['lagna']['Lg']['rashi']
                : 1;

            if (isset($vargaData['graha']) && is_array($vargaData['graha'])) {
                foreach ($vargaData['graha'] as $k => $v) {
                    if (!isset($vargaData['graha'][$k]['rashi'])) {
                        continue;
                    }
                    $grahaRashi = (int) $vargaData['graha'][$k]['rashi'];
                    $vargaData['graha'][$k]['house_number'] = (($grahaRashi - $ascRashi + 12) % 12) + 1;
                }
            }

            return $vargaData;
        }

        $bhava1Varga = $this->getVargaRashi($this->getData()['bhava'][1]);
        $ascRashi = (int) $bhava1Varga['rashi'];
        foreach ($this->getData()['bhava'] as $k => $v) {
            $rashi = $k == 1 ? $bhava1Varga['rashi'] : Math::numberNext($rashi);
            $vargaData['bhava'][$k] = [
                'rashi' => $rashi,
                'degree' => $bhava1Varga['degree'],
                'longitude' => 30 * ($rashi - 1) + $bhava1Varga['degree'],
            ];
        }
        
        foreach ($this->getData()['graha'] as $k => $v) {
            $result = $this->getVargaRashi($v);
            $houseNumber = (($result['rashi'] - $ascRashi + 12) % 12) + 1;
            $vargaData['graha'][$k] = [
                'rashi' => $result['rashi'],
                'degree' => $result['degree'],
                'speed' => $this->getData()['graha'][$k]['speed'],
                'house_number' => $houseNumber,
                'longitude' => 30 * ($result['rashi'] - 1) + $result['degree'],
            ];
        }
        
        foreach ($this->getData()['lagna'] as $k => $v) {
            $result = $this->getVargaRashi($v);
            $vargaData['lagna'][$k] = [
                'rashi' => $result['rashi'],
                'degree' => $result['degree'],
                'longitude' => 30 * ($result['rashi'] - 1) + $result['degree'],
            ];
        }
        return $vargaData;
    }
}
