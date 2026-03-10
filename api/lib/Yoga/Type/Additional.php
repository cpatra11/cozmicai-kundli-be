<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace Jyotish\Yoga\Type;

use Jyotish\Yoga\Yoga;
use Jyotish\Graha\Graha;
use Jyotish\Bhava\Bhava;

/**
 * Collection of additional classical yogas not grouped under existing types.
 *
 * Currently contains examples like Gajakesari, Kemadruma and Vipareeta Raja.
 * More yogas from the MD documentation can be added as needed.
 *
 * @author Extended by assistant
 */
class Additional extends YogaBase
{
    /**
     * Type of yogas.
     *
     * @var string
     */
    protected $yogaType = Yoga::TYPE_ADDITIONAL;

    /**
     * List of combinations to evaluate. Method names will be prefixed with
     * "has" when invoked by the generator.
     *
     * @var array
     */
    public static $yoga = [
        'Gajakesari',
        'Kemadruma',
        'VipareetaRaja',
        'DharmaKarmadhipati',
        'NeechaBhangRaja',
    ];

    /**
     * Jupiter and Moon in kendra from each other (Gaja Kesari Yoga).
     *
     * @return bool
     * @see MD (Gajakesari): "When Moon is in kendra from Jupiter."
     */
    public function hasGajakesari()
    {
        $graha = $this->getData()['graha'];
        $j = $graha[Graha::KEY_GU]['rashi'];
        $m = $graha[Graha::KEY_CH]['rashi'];

        // kendra distance difference: 1,4,7,10 (mod 12)
        $d = ($m - $j + 12) % 12;
        if (in_array($d, [1, 4, 7, 10])) {
            return true;
        }
        return false;
    }

    /**
     * Kemadruma Yoga: Moon alone in its sign (no other graha in same rashi and
     * none in preceding or succeeding sign).
     *
     * @return bool
     */
    public function hasKemadruma()
    {
        $graha = $this->getData()['graha'];
        $mRashi = $graha[Graha::KEY_CH]['rashi'];

        // Check that moon is alone in its rashi
        foreach ($graha as $key => $data) {
            if ($key === Graha::KEY_CH) continue;
            if ($data['rashi'] == $mRashi) {
                return false;
            }
        }

        // preceding and succeeding rashi numbers
        $prev = $mRashi == 1 ? 12 : $mRashi - 1;
        $next = $mRashi == 12 ? 1 : $mRashi + 1;
        foreach ($graha as $data) {
            if ($data['rashi'] == $prev || $data['rashi'] == $next) {
                return false;
            }
        }
        return true;
    }

    /**
     * Helper: return the graha keys that rule a given rashi (zodiac sign).
     *
     * Some signs have dual rulers (e.g. Gemini and Virgo are both Mercury), so
     * we return an array of matching planet keys.  Empty array if no lord found.
     *
     * @param int $rashi 1–12 numeric sign
     * @return string[] graha keys (e.g. ['Ma','Gu'])
     */
    protected function getRashiLords(int $rashi): array
    {
        $lords = [];
        foreach (Graha::listGraha() as $key => $name) {
            $G = Graha::getInstance($key)->setEnvironment($this->Data);
            foreach ($G->grahaSwa as $swa) {
                if (isset($swa['rashi']) && $swa['rashi'] === $rashi) {
                    $lords[] = $key;
                }
            }
        }
        return $lords;
    }

    public function hasVipareetaRaja()
    {
        $bhava = $this->getData()['bhava'];

        // dusthana bhava numbers
        $dusthana = [6, 8, 12];

        // find the ruler (lord) of each dusthana house's rashi
        $lords = [];
        foreach ($dusthana as $b) {
            $rashi = $bhava[$b]['rashi'] ?? null;
            if ($rashi === null) {
                continue;
            }
            $rl = $this->getRashiLords($rashi);
            foreach ($rl as $lord) {
                $lords[] = $lord;
            }
        }

        // if any of these lords currently occupies a dusthana bhava, yoga exists
        foreach ($lords as $lord) {
            $G = Graha::getInstance($lord)->setEnvironment($this->Data);
            $lordBhava = $G->getBhava();
            if (in_array($lordBhava, $dusthana)) {
                $yogaData = $this->assignYoga('VipareetaRaja', '', ['lords' => $lords]);
                return [$yogaData];
            }
        }
        return false;
    }

    /**
     * Dharma-Karmadhipati Yoga: 9th and 10th lords conjunct/parivarthana/aspect
     * each other in a benefic house (a strong Raj yoga subset).
     *
     * @return bool
     */
    public function hasDharmaKarmadhipati()
    {
        $bhava = $this->getData()['bhava'];
        $lords9 = $this->getRashiLords($bhava[9]['rashi'] ?? 0);
        $lords10 = $this->getRashiLords($bhava[10]['rashi'] ?? 0);
        if (empty($lords9) || empty($lords10)) {
            return false;
        }
        // check any combination of the two lord sets for conjunction/aspect
        foreach ($lords9 as $l9) {
            foreach ($lords10 as $l10) {
                $G9 = Graha::getInstance($l9)->setEnvironment($this->Data);
                $G10 = Graha::getInstance($l10)->setEnvironment($this->Data);
                if (
                    $G9->isConjuncted($l10) ||
                    $G9->isAspectedByGraha($l10) ||
                    $G10->isAspectedByGraha($l9)
                ) {
                    $yogaData = $this->assignYoga('DharmaKarmadhipati', '', ['lords' => [$l9, $l10]]);
                    return [$yogaData];
                }
            }
        }
        return false;
    }

    /**
     * Neecha-Bhanga Raja Yoga (simplified): any debilitated planet whose sign
     * lord is placed in a Kendra from the lagna.
     *
     * @return bool
     */
    public function hasNeechaBhangRaja()
    {
        $graha = $this->getData()['graha'];
        foreach ($graha as $key => $p) {
            if (isset($p['rashiAvastha']) && $p['rashiAvastha'] === 'debilitated') {
                $lords = $this->getRashiLords($p['rashi']);
                foreach ($lords as $lord) {
                    $GL = Graha::getInstance($lord)->setEnvironment($this->Data);
                    $lordBhava = $GL->getBhava();
                    if (in_array($lordBhava, Bhava::$bhavaKendra)) {
                        $yogaData = $this->assignYoga('NeechaBhangRaja', '', ['planet' => $key, 'lord' => $lord]);
                        return [$yogaData];
                    }
                }
            }
        }
        return false;
    }
}
