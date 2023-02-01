<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loan;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group api
     */
    public function getSignage()
    {
        $this->seed();

        $this->json('GET', 'api/signage')
            ->assertStatus(200)
            ->assertJson([
                Loan::first()->toArray(), // Loan
                Loan::skip(1)->first()->toArray(), // Setup
            ]);
    }
}