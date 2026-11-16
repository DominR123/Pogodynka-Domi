<?php

namespace App\Command;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city',
    description: 'Wyswietla prognoze pogody dla kodu kraju i miasta',
)]
class WeatherCityCommand{
    public function __construct(
        private WeatherUtil $weatherUtil,
    ) {

    }
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country Code')] string $countryCode,
        #[Argument('City')] string $city,
    ): int {

        $forecasts = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $city);
        $io->title(sprintf('Prognoza pogody dla:%s, %s', $city, $countryCode));
        if (empty($forecasts)) {
            $io->note('Brak pomiarów dla tej lokalizacji');
            return Command::SUCCESS;
        }
        $rows = [];
            foreach($forecasts as $forecast) {
                $rows[] = [
                    $forecast->getDate()?->format('Y-m-d'),
                    $forecast->getTemperature() . '°C',
                    $forecast->getPressure() . 'hPa',
                    $forecast->getRain() . 'mm',

                ];
            }
            $io->table(['Date', 'Temperature', 'Pressure', 'Rain'], $rows);

        return Command::SUCCESS;
    }
}
