<?php

namespace Tests\Unit;

use App\Services\Shipping\DpdApiService;
use Tests\TestCase;

class DpdApiServiceTest extends TestCase
{
    public function test_calculate_package_dimensions_single_package()
    {
        $packages = DpdApiService::calculatePackageDimensions(15);
        
        $this->assertCount(1, $packages);
        $this->assertEquals(300, $packages[0]['weight']); // 15 * 20g
        $this->assertEquals(10, $packages[0]['length']);
        $this->assertEquals(5, $packages[0]['width']);
        $this->assertEquals(8, $packages[0]['height']);
    }

    public function test_calculate_package_dimensions_multiple_packages()
    {
        $packages = DpdApiService::calculatePackageDimensions(50);
        
        $this->assertCount(2, $packages);
        
        // First package: 27 items
        $this->assertEquals(540, $packages[0]['weight']); // 27 * 20g
        
        // Second package: 23 items (50 - 27)
        $this->assertEquals(460, $packages[1]['weight']); // 23 * 20g
    }

    public function test_calculate_package_dimensions_exact_multiple()
    {
        $packages = DpdApiService::calculatePackageDimensions(54); // 27 * 2
        
        $this->assertCount(2, $packages);
        $this->assertEquals(540, $packages[0]['weight']);
        $this->assertEquals(540, $packages[1]['weight']);
    }

    public function test_calculate_package_dimensions_minimum_one_package()
    {
        $packages = DpdApiService::calculatePackageDimensions(0);
        
        $this->assertCount(1, $packages);
        $this->assertEquals(0, $packages[0]['weight']);
    }
}
