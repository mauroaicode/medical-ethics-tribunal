<?php

declare(strict_types=1);

namespace Src\Application\Shared\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Log;

class LocationService
{
    /**
     * Get location from IP address using free geolocation API
     */
    public function getLocationFromIp(string $ip): ?string
    {
        // Skip for localhost or private IPs
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.')) {
            return 'Local';
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? null) === 'success') {
                    $parts = array_filter([
                        $data['city'] ?? null,
                        $data['regionName'] ?? null,
                        $data['country'] ?? null,
                    ]);

                    return $parts === [] ? null : implode(', ', $parts);
                }
            }
        } catch (Exception $e) {
            // Log error silently and return null
            Log::debug("Error getting location for IP {$ip}: {$e->getMessage()}");
        }

        return null;
    }
}
