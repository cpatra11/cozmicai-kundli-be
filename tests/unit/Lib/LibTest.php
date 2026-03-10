<?php

namespace JyotishTest\Lib;

use Jyotish\Lib;

class LibTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group lib
     */
    public function testCalculateNowIncludesNestedDasha()
    {
        $lib = new Lib();

        // use known coordinates and timezone; nesting 4 should produce at least
        // mahadasha+antardasha+pratyantardasha periods if the library works.
        $result = $lib->calculateNow('20.9501', '85.2168', '+05:30', 4);

        $this->assertArrayHasKey('dasha', $result);
        $dasha = $result['dasha'];

        $this->assertArrayHasKey('periods', $dasha);
        $this->assertNotEmpty($dasha['periods']);

        // pick first period and ensure it contains nested periods
        $first = reset($dasha['periods']);
        $this->assertArrayHasKey('periods', $first);
        $this->assertNotEmpty($first['periods']);

        // check second level also has nested periods if we requested 4 levels
        $second = reset($first['periods']);
        if (is_array($second)) {
            $this->assertArrayHasKey('periods', $second);
        }
    }
}
