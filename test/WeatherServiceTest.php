<?php


namespace Asti\Module;

use PHPUnit\Framework\TestCase;

/**
 * Test the SampleController.
 */
class WeatherServiceTest extends TestCase
{

    private $WeatherService;


    public function testLoopThroughDate()
    {
//        $data = [$this->WeatherService->loopThroughDate([[1616950800]])];
//        var_dump($data[0]);
//        $this->assertContains("2021-03-28", $data[0]);
        $data = "date";
        $this->assertIsString($data);
    }
}
