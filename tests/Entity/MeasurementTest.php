<?php

namespace App\Tests\Entity;

use App\Entity\Forecast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class MeasurementTest extends TestCase
{
    #[DataProvider('dataGetFahrenheit')]
    public function testGetFahrenheit(string $celsius, string $expectedFahrenheit): void
    {
        $forecast = new Forecast();
        $forecast->setTemperature((float)$celsius);
        $this->assertEqualsWithDelta((float)$expectedFahrenheit, $forecast->getFahrenheit(), 0.01);
        }
    public static function dataGetFahrenheit(): array
    {
        return [
            ['0', '32'],
            ['-100', '-148'],
            ['100', '212'],
            ['0.5', '32.9'],
            ['11', '51.8'],
            ['-0.5', '31.1'],
            ['2.5', '36.5'],
            ['-40', '-40'],
            ['21', '69.8'],
            ['80', '176'],
        ];
    }
}
