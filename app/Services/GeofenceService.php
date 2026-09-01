<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves how far a coordinate is from the configured office locations.
 *
 * The office list is small and rarely changes, so it is cached and evaluated in
 * memory instead of hitting the database on every check-in.
 */
class GeofenceService
{
    public const CACHE_KEY = 'geofence.active_companies';

    public const CACHE_TTL = 600;

    private const EARTH_RADIUS_METERS = 6371000.0;

    /**
     * Haversine distance between two coordinates, in meters.
     */
    public function distanceInMeters(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        $latFrom = deg2rad($latitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $deltaLat = $latTo - $latFrom;
        $deltaLon = deg2rad($longitudeTo) - deg2rad($longitudeFrom);

        $a = sin($deltaLat / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($deltaLon / 2) ** 2;

        return self::EARTH_RADIUS_METERS * 2 * asin(min(1.0, sqrt($a)));
    }

    /**
     * Active offices with usable coordinates, cached for a short period.
     *
     * @return Collection<int, Company>
     */
    public function activeCompanies(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): Collection {
            return Company::query()
                ->where('is_active', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('name')
                ->get()
                ->filter(fn (Company $company): bool => $company->hasCoordinates())
                ->values();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Offices the given user is allowed to check in at.
     *
     * A user assigned to an office is bound to it; unassigned users may use any
     * active office (multi-site companies rely on this).
     *
     * @return Collection<int, Company>
     */
    public function companiesFor(User $user): Collection
    {
        $companies = $this->activeCompanies();

        if ($user->company_id === null) {
            return $companies;
        }

        $assigned = $companies->firstWhere('id', $user->company_id);

        return $assigned ? collect([$assigned]) : collect();
    }

    /**
     * Evaluate a coordinate against every office available to the user.
     *
     * @return array{
     *     nearest: Company|null,
     *     distance_meters: float|null,
     *     within_radius: bool,
     *     matched: Company|null,
     *     locations: array<int, array<string, mixed>>
     * }
     */
    public function evaluate(User $user, float $latitude, float $longitude): array
    {
        $companies = $this->companiesFor($user);

        $nearest = null;
        $nearestDistance = null;
        $matched = null;
        $matchedDistance = null;
        $locations = [];

        foreach ($companies as $company) {
            $distance = $this->distanceInMeters(
                $latitude,
                $longitude,
                (float) $company->latitude,
                (float) $company->longitude
            );

            $radius = $company->radiusInMeters();
            $withinRadius = $distance <= $radius;

            $locations[] = [
                'id' => $company->id,
                'name' => $company->name,
                'address' => $company->address,
                'latitude' => (float) $company->latitude,
                'longitude' => (float) $company->longitude,
                'radius_meters' => $radius,
                'distance_meters' => round($distance, 2),
                'within_radius' => $withinRadius,
            ];

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $company;
            }

            if ($withinRadius && ($matchedDistance === null || $distance < $matchedDistance)) {
                $matchedDistance = $distance;
                $matched = $company;
            }
        }

        usort($locations, fn (array $a, array $b): int => $a['distance_meters'] <=> $b['distance_meters']);

        return [
            'nearest' => $nearest,
            'distance_meters' => $nearestDistance === null ? null : round($nearestDistance, 2),
            'within_radius' => $matched !== null,
            'matched' => $matched,
            'locations' => $locations,
        ];
    }
}
