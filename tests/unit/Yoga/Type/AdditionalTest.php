<?php
/**
 * @link      http://github.com/kunjara/jyotish for the canonical source repository
 * @license   GNU General Public License version 2 or later
 */

namespace JyotishTest\Yoga\Type;

use Jyotish\Yoga\Type\Additional;
use Jyotish\Base\Data;
use Jyotish\Base\Import\ArraySource;

/**
 * @group yoga
 */
class AdditionalTest extends \PHPUnit_Framework_TestCase
{
    private $dataSource;

    public function setUp()
    {
        parent::setUp();
        require 'data/array-additional.php';
        $this->dataSource = $dataSource;
    }

    /**
     * @covers Jyotish\Yoga\Type\Additional::hasGajakesari
     * @covers Jyotish\Yoga\Type\Additional::hasKemadruma
     * @covers Jyotish\Yoga\Type\Additional::hasVipareetaRaja
     */
    public function testAdditionalYogas()
    {
        $Additional = new Additional();

        // test Gajakesari config
        $Source = new ArraySource($this->dataSource->Gajakesari);
        $Data = Data::createFromImport($Source);
        $Additional->setDataInstance($Data);
        $this->assertTrue($Additional->hasGajakesari(), 'expected Gajakesari to be detected');
        $this->assertFalse($Additional->hasKemadruma());
        $this->assertFalse($Additional->hasVipareetaRaja());

        // test Kemadruma config
        $Source = new ArraySource($this->dataSource->Kemadruma);
        $Data = Data::createFromImport($Source);
        $Additional->setDataInstance($Data);
        $this->assertFalse($Additional->hasGajakesari());
        $this->assertTrue($Additional->hasKemadruma(), 'expected Kemadruma to be detected');
        $this->assertFalse($Additional->hasVipareetaRaja());

        // test Vipareeta Raja config
        $Source = new ArraySource($this->dataSource->VipareetaRaja);
        $Data = Data::createFromImport($Source);
        $Additional->setDataInstance($Data);
        $this->assertFalse($Additional->hasGajakesari());
        $this->assertFalse($Additional->hasKemadruma());
        $this->assertTrue($Additional->hasVipareetaRaja(), 'expected Vipareeta Raja to be detected');

        // ensure our rashi‑lord helper returns something sensible for the first
        // dusthana sign present in the test data (demonstrates planet lordship logic)
        $TestHelper = new class extends Additional { public function publicRashiLords($r) { return $this->getRashiLords($r); } };
        $TestHelper->setDataInstance($Data);
        $firstDust = 6;
        $rashi6 = $Data->getData()['bhava'][$firstDust]['rashi'];
        $lords = $TestHelper->publicRashiLords($rashi6);
        $this->assertNotEmpty($lords, "rashi {$rashi6} should have at least one lord");

        // Dharma-Karmadhipati scenario should also be recognized
        $Source = new ArraySource($this->dataSource->DharmaKarmadhipati);
        $Data2 = Data::createFromImport($Source);
        $Additional->setDataInstance($Data2);
        $this->assertTrue($Additional->hasDharmaKarmadhipati(), 'expected Dharma-Karmadhipati Yoga');

        // Neecha-Bhang Raja scenario
        $Source = new ArraySource($this->dataSource->NeechaBhangRaja);
        $Data3 = Data::createFromImport($Source);
        $Additional->setDataInstance($Data3);
        $this->assertTrue($Additional->hasNeechaBhangRaja(), 'expected Neecha-Bhang Raja Yoga');

    }
}
