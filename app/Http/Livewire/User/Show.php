<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\User;
use App\Models\Loan;
use App\Models\Setup;

class Show extends Component
{
    use WithPerPagePagination, WithSorting;

    public $user;

    protected $paginationTheme = 'bootstrap';

    public $showFilters = false;

    public $filters = [
        'search' => '',
        'name' => null,
        'tag' => null,
        'description' => null,
    ];

    protected $queryString = [];

    public function resetFilters()
    {
        $this->reset('filters');
    }

    public function getRowsQueryProperty()
    {
        $user = $this->user;

        $query = Loan::query()
            ->whereHas('user', function($query) use($user){
                $query->where('user_id', '=', $user->id);
            })
            ->when($this->filters['search'], fn($query, $search) => $query->where('forename', 'like', '%'.$search.'%'));

        return $this->applySorting($query);
    }

    public function updatedFilters($filed)
    {
        $this->resetPage();
    }

    public function getRowsProperty()
    {
        return $this->applyPagination($this->rowsQuery);
    }

    public function render()
    {
        return view('livewire.user.show', [
            'loans' => $this->rows,
        ]);
    }

    public function mount($user)
    {
        $this->user = User::find($user);
    }
}