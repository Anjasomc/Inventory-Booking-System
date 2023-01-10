<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Role;
use App\Models\Loan;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class LoansPageTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function loans_page_contains_livewire_component()
    {
        //Make user
        $user = User::factory()->withPasswordSet()->create();
        Role::factory()->withUser($user)->create();

        //Perform Login
        Livewire::test('auth.login')
            ->set('email', 'admin@admin123.com')
            ->set('password', '1234')
            ->call('login')
            ->assertRedirect('/loans');

        $this->get('/loans')->assertSeeLivewire('loan.loans');
    }

    /** @test */
    public function can_open_create_loan_model()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('loan.loans')
            ->call('showModal')
            ->assertEmitted('showModal');
    }

    /** @test */
    public function can_create_loan()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', $id)
            ->set('editing.status_id', 0)
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasNoErrors();
    }

    /** @test */
    public function can_see_created_loan_in_table()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', $id)
            ->set('editing.status_id', 0)
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertSee(Loan::first()->id);
    }

    /** @test */
    public function user_id_is_required()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.status_id', 0)
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.user_id' => 'required']);
    }

    /** @test */
    public function user_id_is_integer()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 'Apples')
            ->set('editing.status_id', 0)
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.user_id' => 'integer']);
    }

    /** @test */
    public function status_id_is_required()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 0)
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.status_id' => 'required']);
    }

    /** @test */
    public function status_id_is_integer()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 0)
            ->set('editing.status_id', 'Apples')
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.status_id' => 'integer']);
    }

    /** @test */
    public function status_id_is_0_or_1()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 0)
            ->set('editing.status_id', '3')
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', "This is a test loan")
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.status_id' => 'in:0,1']);
    }

    /** @test */
    public function details_is_string()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 0)
            ->set('editing.status_id', '3')
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', 0)
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasErrors(['editing.details' => 'string']);
    }

    /** @test */
    public function details_can_be_null()
    {
        Artisan::call('db:seed');
        $this->actingAs(User::factory()->create());
        $id = User::first()->id;

        //Create Loan
        Livewire::test('loan.loans')
            ->set('editing.user_id', 0)
            ->set('editing.status_id', '3')
            ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
            ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
            ->set('editing.details', null)
            ->set('equipment_id', Asset::first()->id)
            ->call('save')
            ->assertHasNoErrors(['editing.details' => 'nullable']);
    }

    // /** @test */
    // public function equipment_id_exists_in_assets_table()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', 0)
    //         ->set('equipment_id', 0)
    //         ->call('save')
    //         ->assertHasErrors(['editing.equipment_id' => 'exists:assets,id']);
    // }

    // /** @test */
    // public function equipment_id_is_integer()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:14'))
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', 0)
    //         ->set('equipment_id', "Apples")
    //         ->call('save')
    //         ->assertHasErrors(['editing.equipment_id' => 'integer']);
    // }


    // /** @test */
    // public function end_date_time_is_required()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.end_date_time', '')
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.end_date_time' => 'required']);
    // }

    // /** @test */
    // public function end_date_time_is_date()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.end_date_time', "Apples")
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.end_date_time' => 'date']);
    // }

    // /** @test */
    // public function end_date_time_is_after_start_date_time()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('11 Jan 2023 13:14'))
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.end_date_time' => 'after:editing.start_date_time']);
    // }

    // /** @test */
    // public function start_date_time_is_required()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', '')
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.start_date_time' => 'required']);
    // }

    // /** @test */
    // public function start_date_time_is_date()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', "Apples")
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.start_date_time' => 'date']);
    // }

    // /** @test */
    // public function start_date_time_is_before_end_date_time()
    // {
    //     Artisan::call('db:seed');
    //     $this->actingAs(User::factory()->create());
    //     $id = User::first()->id;

    //     //Create Loan
    //     Livewire::test('loan.loans')
    //         ->set('editing.user_id', 0)
    //         ->set('editing.status_id', '3')
    //         ->set('editing.start_date_time', Carbon::parse('11 Jan 2023 13:14'))
    //         ->set('editing.end_date_time', Carbon::parse('10 Jan 2023 13:15'))
    //         ->set('editing.details', "This is a test loan")
    //         ->set('equipment_id', Asset::first()->id)
    //         ->call('save')
    //         ->assertHasErrors(['editing.start_date_time' => 'before:editing.end_date_time']);
    // }
}
