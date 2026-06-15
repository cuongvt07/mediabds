<?php

namespace Tests\Feature;

use App\Models\RealEstateListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_filter_and_open_a_room_listing(): void
    {
        $listing = RealEstateListing::create([
            'title' => 'Studio đầy đủ nội thất tại Quận 1',
            'type' => 'Cho thuê',
            'property_type' => 115,
            'room_type' => 'studio',
            'furnish' => 'full',
            'province_id' => '79',
            'province_name' => 'Hồ Chí Minh',
            'district_id' => '760',
            'district_name' => 'Quận 1',
            'ward_id' => '26734',
            'ward_name' => 'Phường Bến Nghé',
            'price' => 4800000,
            'price_unit' => '2',
            'contact_phone' => '0901234567',
            'is_sold' => false,
            'status' => 'active',
        ]);

        $this->get('/?district=760&room_type=studio&furnish=full')
            ->assertOk()
            ->assertSee($listing->title)
            ->assertSee('Phường Bến Nghé');

        $this->get(route('site.listings.show', $listing))
            ->assertOk()
            ->assertSee($listing->title)
            ->assertSee('0901234567');
    }
}
