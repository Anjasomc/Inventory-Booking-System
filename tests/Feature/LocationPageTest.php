<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;
use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Location;
use App\Models\Setup;

class LocationPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group location-search
     */
    public function search_by_id_number()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Setup::first()->location()->first()->id])
            ->set('filters.search', Setup::first()->id)
            ->assertDontSee('No loans found')
            ->assertSeeHtml('"/setups/'.Setup::first()->id.'"')
            ->assertDontSeeHtml('"/setups/'.Setup::skip(1)->first()->id.'"');
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_id_string()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Setup::first()->location()->first()->id])
            ->set('filters.search', '#'.Setup::first()->id)
            ->assertDontSee('No loans found')
            ->assertSeeHtml('"/setups/'.Setup::first()->id.'"')
            ->assertDontSeeHtml('"/setups/'.Setup::skip(1)->first()->id.'"');
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_status()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Location::first()->id])
            ->set('filters.search', 'setup')
            ->assertDontSee('No loans found')
            ->assertSee('Setup');
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_partial_status()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Location::first()->id])
            ->set('filters.search', 'set')
            ->assertDontSee('No loans found')
            ->assertSee('Setup');
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_date()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Location::first()->id])
            ->set('filters.search', Carbon::now()->isoFormat('D MMM YYYY'))
            ->assertDontSee('No loans found')
            ->assertSee(Carbon::now()->isoFormat('D MMM YYYY'));
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_time()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Location::first()->id])
            ->set('filters.search', Carbon::now()->isoFormat('HH:mm'))
            ->assertDontSee('No loans found')
            ->assertSee(Carbon::now()->isoFormat('HH:mm'));
    }

    /**
     * @test
     * @group location-search
     */
    public function search_by_details()
    {
        $this->seed();

        Livewire::test('location.show', ['location' => Setup::first()->location()->first()->id])
            ->set('filters.search', Setup::first()->loan()->first()->details)
            ->assertDontSee('No loans found')
            ->assertSee(Setup::first()->loan()->first()->details);
    }
}
