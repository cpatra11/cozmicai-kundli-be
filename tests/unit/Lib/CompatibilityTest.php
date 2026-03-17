<?php

namespace JyotishTest\Lib;

use App\Lib\Compatibility;

class CompatibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group lib
     */
    public function testRuleEngineMatchesLegacyCsvForSample()
    {
        $csv = Compatibility::match(1, 1, 1, 1, null, [], 'csv');
        $rules = Compatibility::match(1, 1, 1, 1);

        $this->assertNotEmpty($csv, 'Expected CSV match results');
        $this->assertNotEmpty($rules, 'Expected rule engine match results');

        $this->assertSame($csv[0]['score'], $rules[0]['score']);
        $this->assertSame($csv[0]['ashtakoota']['values'], $rules[0]['ashtakoota']['values']);
    }
}
