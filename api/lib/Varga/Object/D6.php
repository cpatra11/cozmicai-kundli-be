<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Varga\Object;

use Jyotish\Ganita\Math;

/**
 * Class of varga D6.
 *
 * @author Kunjara Lila das <vladya108@gmail.com>
 */
class D6 extends AbstractVarga
{
    /**
     * Key of the varga.
     * 
     * @var string
     */
    protected $vargaKey = 'D6';

    /**
     * Names of the varga.
     * 
     * @var array
     */
    protected $vargaNames = [
        'Shashtamsha',
    ];

    /**
     * The number of parts.
     * 
     * @var int
     */
    protected $vargaAmsha = 6;

    /**
     * Get varga rashi.
     * 
     * @param array $ganitaRashi
     * @return array
     * @see Maharishi Parashara. Brihat Parashara Hora Shastra. Chapter 6, Verse 17.
     */
    public function getVargaRashi(array $ganitaRashi)
    {
        $amshaSize = 30 / $this->vargaAmsha;
        $result = $this->getAmshaResult($ganitaRashi['degree'], $amshaSize);
        
        $vargaRashi = [];
        $vargaRashi['degree'] = $result['parts'] * 30 / $amshaSize;

        // Odd signs (1,3,5,7,9,11): parts 1-6 → Aries(1) through Virgo(6)
        // Even signs (2,4,6,8,10,12): parts 1-6 → Libra(7) through Pisces(12)
        $vargaRashi['rashi'] = $ganitaRashi['rashi'] % 2
            ? $result['units'] + 1
            : $result['units'] + 7;

        return $vargaRashi;
    }
}
