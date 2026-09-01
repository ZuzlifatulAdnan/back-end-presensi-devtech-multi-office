<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Services\GeofenceService;
use PHPUnit\Framework\TestCase;

class GeofenceServiceTest extends TestCase
{
    private GeofenceService $geofence;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geofence = new GeofenceService;
    }

    public function test_distance_between_identical_coordinates_is_zero(): void
    {
        $distance = $this->geofence->distanceInMeters(-6.208763, 106.845599, -6.208763, 106.845599);

        $this->assertSame(0.0, round($distance, 6));
    }

    public function test_distance_is_measured_in_meters(): void
    {
        // Monas to Jakarta Kota station is roughly 4.9 km.
        $distance = $this->geofence->distanceInMeters(-6.175392, 106.827153, -6.137658, 106.814156);

        $this->assertGreaterThan(4000, $distance);
        $this->assertLessThan(5500, $distance);
    }

    public function test_distance_is_symmetric(): void
    {
        $forward = $this->geofence->distanceInMeters(-6.20, 106.80, -6.30, 106.90);
        $backward = $this->geofence->distanceInMeters(-6.30, 106.90, -6.20, 106.80);

        $this->assertSame(round($forward, 6), round($backward, 6));
    }

    public function test_radius_falls_back_to_a_safe_default_when_misconfigured(): void
    {
        $company = new Company(['radius_km' => 0]);

        $this->assertSame(100.0, $company->radiusInMeters());
    }

    public function test_radius_is_converted_from_kilometres_to_metres(): void
    {
        $company = new Company(['radius_km' => '0.5']);

        $this->assertSame(500.0, $company->radiusInMeters());
    }

    public function test_company_without_numeric_coordinates_is_not_usable_for_geofencing(): void
    {
        $this->assertFalse((new Company(['latitude' => '', 'longitude' => '']))->hasCoordinates());
        $this->assertTrue((new Company(['latitude' => '-6.2', 'longitude' => '106.8']))->hasCoordinates());
    }
}
