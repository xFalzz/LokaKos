<?php

namespace App\Services;

use App\Models\BoardingHouse;
use Illuminate\Support\Facades\Http;

class GoogleMapsResolver
{
    private const ALLOWED_HOSTS = [
        'maps.app.goo.gl',
        'goo.gl',
        'google.com',
        'www.google.com',
        'maps.google.com',
        'google.co.id',
        'www.google.co.id',
        'maps.google.co.id',
    ];

    public function resolve(string $input): ?array
    {
        $raw = trim($input);
        if ($raw === '') {
            return null;
        }

        if ($coords = $this->extractCoordinates($raw)) {
            return $this->formatResult($coords['lat'], $coords['lng'], $this->isGoogleMapsUrl($raw) ? $raw : null, 'direct');
        }

        if ($this->isGoogleMapsUrl($raw)) {
            $expanded = $this->expandGoogleMapsUrl($raw);
            if ($expanded) {
                if ($coords = $this->extractCoordinates($expanded['url'])) {
                    return $this->formatResult($coords['lat'], $coords['lng'], $expanded['url'], 'gmaps_redirect');
                }
                if (! empty($expanded['body']) && ($coords = $this->extractCoordinatesFromHtml($expanded['body']))) {
                    return $this->formatResult($coords['lat'], $coords['lng'], $expanded['url'], 'gmaps_page');
                }
                if ($placeQuery = $this->extractPlaceNameFromUrl($expanded['url'])) {
                    if ($geo = $this->geocodeAddress($placeQuery)) {
                        return $this->formatResult($geo['lat'], $geo['lng'], $expanded['url'], 'gmaps_place_geocode', $geo['display_name'] ?? null);
                    }
                }
            }

            return null;
        }

        if ($geo = $this->geocodeAddress($raw)) {
            return $this->formatResult($geo['lat'], $geo['lng'], null, 'address_geocode', $geo['display_name'] ?? null);
        }

        return null;
    }

    public function extractCoordinates(string $text): ?array
    {
        $decoded = urldecode($text);

        if (preg_match('/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/', $decoded, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        if (preg_match('/!2d(-?\d{1,3}\.\d+)!3d(-?\d{1,2}\.\d+)/', $decoded, $m)) {
            return $this->validateCoords((float) $m[2], (float) $m[1]);
        }

        if (preg_match('/[?&](?:q|ll|query|center|destination|daddr)=(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)/i', $decoded, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        if (preg_match('/@(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)/', $decoded, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        if ($dms = $this->parseDms($decoded)) {
            return $dms;
        }

        if (preg_match('/^\s*(-?\d{1,2}\.\d{3,})\s*[,\s]\s*(-?\d{1,3}\.\d{3,})\s*$/', $decoded, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        return null;
    }

    public function isGoogleMapsUrl(string $value): bool
    {
        if (! preg_match('/^https?:\/\//i', $value)) {
            return false;
        }

        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        return in_array($host, self::ALLOWED_HOSTS, true);
    }

    private function expandGoogleMapsUrl(string $url): ?array
    {
        $current = $url;
        $body = '';

        for ($hop = 0; $hop < 6; $hop++) {
            if (! $this->isGoogleMapsUrl($current)) {
                return null;
            }

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
                ])
                    ->withOptions(['allow_redirects' => false])
                    ->timeout(8)
                    ->get($current);
            } catch (\Throwable) {
                return null;
            }

            if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                $location = (string) $response->header('Location');
                if ($location === '') {
                    break;
                }
                if (str_starts_with($location, '/')) {
                    $parts = parse_url($current);
                    $location = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'www.google.com') . $location;
                }
                $current = $location;
                if ($this->extractCoordinates($current)) {
                    return ['url' => $current, 'body' => ''];
                }

                continue;
            }

            $body = (string) $response->body();
            break;
        }

        return ['url' => $current, 'body' => $body];
    }

    private function extractCoordinatesFromHtml(string $html): ?array
    {
        if (preg_match('/!3d(-?\d{1,2}\.\d+)!4d(-?\d{1,3}\.\d+)/', $html, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        if (preg_match('/center=(-?\d{1,2}\.\d+)(?:%2C|,)(-?\d{1,3}\.\d+)/i', $html, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        if (preg_match('/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $html, $m)) {
            return $this->validateCoords((float) $m[1], (float) $m[2]);
        }

        return null;
    }

    private function extractPlaceNameFromUrl(string $url): ?string
    {
        $decoded = urldecode($url);

        if (preg_match('/\/maps\/(?:place|search)\/([^\/?@]+)/i', $decoded, $m)) {
            $place = trim(str_replace('+', ' ', $m[1]));

            return $place !== '' ? $place : null;
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $q = trim((string) ($query['q'] ?? $query['query'] ?? ''));

        return $q !== '' ? $q : null;
    }

    private function geocodeAddress(string $address): ?array
    {
        $query = trim($address);
        if (mb_strlen($query) < 4) {
            return null;
        }

        if (! preg_match('/yogyakarta|jogja|bantul|sleman|kasihan|tamantirto|umy/i', $query)) {
            $query .= ', Bantul, Yogyakarta';
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'LokaKosUMY/1.0 (Academic Boarding House Directory)',
                'Accept-Language' => 'id',
            ])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'id',
                    'q' => $query,
                ]);

            if (! $response->ok()) {
                return null;
            }

            $first = $response->json()[0] ?? null;
            if (! is_array($first) || ! isset($first['lat'], $first['lon'])) {
                return null;
            }

            $coords = $this->validateCoords((float) $first['lat'], (float) $first['lon']);
            if (! $coords) {
                return null;
            }

            return [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'display_name' => (string) ($first['display_name'] ?? ''),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDms(string $text): ?array
    {
        $pattern = '/(\d{1,2})\s*(?:°|d)\s*(\d{1,2})\s*(?:\'|′|m)\s*(\d{1,2}(?:\.\d+)?)\s*(?:"|″|\'\'|s)?\s*([NS])\s*[,\s]+\s*(\d{1,3})\s*(?:°|d)\s*(\d{1,2})\s*(?:\'|′|m)\s*(\d{1,2}(?:\.\d+)?)\s*(?:"|″|\'\'|s)?\s*([EW])/iu';
        if (! preg_match($pattern, $text, $m)) {
            return null;
        }

        $lat = (float) $m[1] + ((float) $m[2] / 60) + ((float) $m[3] / 3600);
        if (strtoupper($m[4]) === 'S') {
            $lat = -$lat;
        }

        $lng = (float) $m[5] + ((float) $m[6] / 60) + ((float) $m[7] / 3600);
        if (strtoupper($m[8]) === 'W') {
            $lng = -$lng;
        }

        return $this->validateCoords($lat, $lng);
    }

    private function validateCoords(float $lat, float $lng): ?array
    {
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [
            'lat' => round($lat, 7),
            'lng' => round($lng, 7),
        ];
    }

    private function formatResult(
        float $lat,
        float $lng,
        ?string $resolvedUrl,
        string $source,
        ?string $displayName = null
    ): array {
        $probe = new BoardingHouse([
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
        $distance = $probe->calculateDistanceToUmy();
        $probe->distance_to_umy_km = $distance;

        return [
            'lat' => round($lat, 7),
            'lng' => round($lng, 7),
            'distance_km' => $distance,
            'walking_minutes' => $probe->estimatedWalkingMinutes(),
            'motor_minutes' => $probe->estimatedMotorMinutes(),
            'resolved_url' => $resolvedUrl ?: "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}",
            'source' => $source,
            'display_name' => $displayName,
            'far_from_umy' => $distance !== null && $distance > 25,
        ];
    }
}
