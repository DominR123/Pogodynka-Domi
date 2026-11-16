<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:location',
    description: 'Pobiera prognoze dla lokalizacji',
)]
class WeatherLocationCommand{
    public function __construct(
        private LocationRepository $locationRepository,
        private WeatherUtil $weatherUtil,
    ){}
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Location ID')] int $id,
    ): int {
        $location = $this->locationRepository->find($id);
        if(!$location){
            $io->error("Nie znaleziono lokalizacji");
            return Command::FAILURE;
        }
        $forecasts = $this->weatherUtil->getWeatherForLocation($location);
        $io->title(printf('Pogoda dla: %s, %s', $location->getCity(), $location->getCountry()));
        if(empty($forecasts)){
            $io->note("Brak pomiarów");
            return Command::SUCCESS;
        }
        $rows = [];
        foreach ($forecasts as $forecast) {
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
