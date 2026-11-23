<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Service\WeatherUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        WeatherUtil $weatherUtil,
        #[MapQueryParameter] ?string $country = null,
        #[MapQueryParameter] ?string $city = null,
        #[MapQueryParameter] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
    ): Response
    {
        $forecasts = ($country !== null && $city !== null)
            ? $weatherUtil->getWeatherForCountryAndCity($country, $city)
            : [];
        $measurements = array_map(fn(Forecast $f) => [
            'date' => $f->getDate()?->format('Y-m-d'),
            'celsius' => $f->getCelsius(),
            'fahrenheit' => $f->getFahrenheit(),
        ], $forecasts);
        if ($format === 'csv') {
            if($twig) {
                return $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ], new Response('', Response::HTTP_OK, [
                    'Content-Type' => 'text/csv; charset=utf-8',
                ]));
            }
            $lines = [
                implode(',',['city', 'country', 'date', 'celsius', 'fahrenheit']),
            ];
            foreach ($measurements as $measurement) {
                $lines[] = sprintf(
                    '%s,%s,%s,%s,%s',
                    $city ?? '',
                    $country ?? '',
                    $measurement['date'] ?? '',
                    $measurement['celsius'] ?? '',
                    $measurement['fahrenheit'] ?? '',
                );
            }
            return new Response(
                implode("\n", $lines),
                Response::HTTP_OK,
                ['Content-Type' => 'text/csv; charset=utf-8'],
            );
        }
        if ($twig) {
            return $this->render('weather_api/index.json.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ], new Response('', Response::HTTP_OK, [
                'Content-Type' => 'application/json; charset=utf-8',
            ]));
        }
        return new JsonResponse([
            'city' => $city,
            'country' => $country,
            'measurements' => $measurements,
        ]);
    }
}
