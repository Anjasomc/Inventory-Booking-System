<?php

namespace App\Http\Livewire\Loan;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\ShoppingCart\WithShoppingCart;
use App\Models\Loan;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Mail\LoanCreated;
use Carbon\Carbon;

class Loans extends Component
{
    use WithPerPagePagination, WithSorting, WithBulkActions, WithShoppingCart;

    protected $paginationTheme = 'bootstrap';       #Use boostrap theme when displaying data with pagination
    protected $queryString = [];                    #Use on tables when displaying data based on the user request
    public $showFilters = false;                    #These are displayed above each column in the table
    public $filters = [                             #Our default filters to apply to the tables
        'search' => '',
        'id' => null,
        'user_id' => null,
        'status_id' => null,
        'start_date_time' => null,
        'end_date_time' => null,
        'details' => null,
        'assets' => null,
    ];

    public Loan $editing;                           #Data relating to the current loan excluding any assets

    public $equipment_id;                           #Used to trigger update on the select2 dropdown as we cannot use wire:model due to wire:ignore in place
    public $counter;                                #???

    public $iteration = 0;                          #Increment anytime we want the Select2 Dropdown to update (as we are using wire:ignore to stop it updating each render)

    public $selectedTagId = "Test";

    public $modalType;                               #Whether the user is creating/edit a loan so we can get correct wording

    public function updatedSelectedTagId()
    {
        dd($this->selectedTagId);
    }

    public function rules()
    {
        return [
            'editing.user_id' => 'required|integer',
            'editing.status_id' => 'required|integer|in:0,1',
            'editing.start_date_time' => 'required|nullable',
            'editing.end_date_time' => 'required|date|after:editing.start_date_time|nullable',
            'editing.details' => 'nullable|string',
            'equipment_id' => 'nullable|numeric|exists:assets,id',
        ];
    }

    public function showModal()
    {
        $this->emit('showModal');
    }

    public function mount()
    {
        $this->makeBlankLoan();
        $this->users = User::latest()->get();
    }

    public function updatedFilters($filed)
    {
        $this->resetPage();
    }

    public function makeBlankLoan()
    {
        $this->editing = Loan::make();
        $shoppingCart = [];
        $equipment_id = null;
        $this->emptyCart();
        $this->iteration ++;
    }

    public function deleteSelected()
    {
        $this->selectedRowsQuery->delete();

        $this->emit('hideModal', 'confirm');
    }

    public function exportSelected()
    {
        return response()->streamDownload(function() {
            echo $this->selectedRowsQuery->toCsv();
        }, 'loans.csv');
    }

    public function create()
    {
        if ($this->editing->getKey()){
        }
        $this->makeBlankLoan();

        $this->modalType = "Create";
        $this->emit('showModal', 'create');
    }

    public function edit(Loan $loan)
    {
        #If the loan is the same as the previous loan that we have stored, just show the modal
        #in the current state that is was when it was last closed rather than wiping the data.
        $this->emptyCart();
        $this->editing = $loan;

        if($this->editing->isNot($loan)){
        }

        //dd($this->shoppingCart);

        //Load assets from loans model into the shopping cart
        $this->editing->assets->each(function ($item, $key) {
            $this->addItemToCart($item, false);
        });

        //Display the modal to the user
        $this->modalType = "Edit";
        $this->emit('showModal', 'edit');

        //Populate equipment dropdown
        $this->getBookableEquipment();
        $this->iteration ++;
    }

    public function save()
    {
        //Make sure all the data the user entered into the modal is valid
        $this->validate();

        //Update database
        $this->editing->start_date_time = carbon::parse($this->editing->start_date_time);
        $this->editing->end_date_time = carbon::parse($this->editing->end_date_time);

        $this->editing->push();

        //Update assets in database
        $loan = Loan::find($this->editing->id);
        $ids = [];
        foreach($this->shoppingCart as $key => $item){
            $ids[$item['id']] = ['returned' => $item['pivot']['returned']];
        }
        $loan->assets()->sync($ids);

        //Hide the modal from the user
        $this->emit('hideModal', 'edit');

        //Send the email to the user
        Mail::to($user->email)->queue(new LoanCreated($this->editing, $this->editing->wasRecentlyCreated));
    }

    public function resetFilters()
    {
        $this->reset('filters');
    }

    public function getRowsQueryProperty()
    {
        $query = Loan::query()
            ->with('user')
            ->with('assets')
            ->where('status_id', '<>', 3) #Setups
            ->where('status_id', '<>', 4) #Cancelled bookings
            ->where('status_id', '<>', 5) #Completed bookings
            ->when($this->filters['user_id'], fn($query, $user_id) => $query->where('user_id', $user_id))
            ->when($this->filters['status_id'], fn($query, $status_id) => $query->where('status_id', $status_id))
            ->when($this->filters['start_date_time'], fn($query, $start_date_time) => $query->where('start_date_time', $start_date_time))
            ->when($this->filters['end_date_time'], fn($query, $end_date_time) => $query->where('end_date_time', $end_date_time))
            ->when($this->filters['details'], fn($query, $details) => $query->where('details', $details))
            ->when($this->filters['search'], fn($query, $search) => $query->where('details', 'like', '%'.$search.'%'));

        return $this->applySorting($query);
    }

    public function getRowsProperty()
    {
        return $this->applyPagination($this->rowsQuery);
    }

    public function render()
    {
        if($this->selectAll){
           $this->selectPageRows();
        }

        return view('livewire.loan.loans', [
            'loans' => $this->rows,
        ]);
    }

    public function removeItem($id)
    {
        $this->removeItemFromCart($id);

        foreach($this->equipmentList as $key => $equipment){
            if($equipment['id'] == $id){
                $this->equipmentList[$key]['avaliable'] = true;
            }
        }

        $this->iteration ++;
    }

    public function isAssetPartofLoan($id)
    {
        $loan = Loan::find($this->editing->id);

        if($loan !== null){
            return $assetPresentInLoan = Loan::find($this->editing->id)->whereHas('assets', function($query) use ($id){
                $query->where('asset_id', $id);
            });
        }

        return false;
    }

    public function bookSingleItem($id)
    {
        //Make sure id is part of the booking before making as returned
        //New items added to the cart should be ignored

        if($this->isAssetPartofLoan($id)){
            foreach($this->shoppingCart as $key => $equipment){
                if($equipment['id'] == $id){
                    if($this->shoppingCart[$key]['pivot']['returned'] == 1){
                        $this->shoppingCart[$key]['pivot']['returned'] = 0;
                    }else{
                        $this->shoppingCart[$key]['pivot']['returned'] = 1;
                    }
                }
            }
        }

        $this->iteration ++;
    }

    public function book($id){
        $loan = Loan::find($id);
        $loan->status_id = 0;
        $loan->push();

        //TODO: Send email stuff
    }

    public function cancel($id){
        $this->updateLoanStatus($id, 4);

        //TODO: Send email stuff
    }

    public function complete($id){
        $this->updateLoanStatus($id, 5);

        //TODO: Send email stuff
    }

    protected function updateLoanStatus($id, $status)
    {
        $loan = Loan::find($id);
        $loan->status_id = $status;
        $loan->push();

        //Update assets in database
        $ids = [];
        foreach($loan->assets as $key => $asset){
            array_push($ids, ['loan_id' => $id, 'asset_id' => $asset['id'], 'returned' => 1]);
        }
        $loan->assets()->sync($ids);
    }

    public function updatedEquipmentId($id)
    {
        $item = Asset::find($id);

        if($this->isAssetPartofLoan($id)){
            $this->addItemToCart($item, false);
        }else{
            $this->addItemToCart($item, true);
        }

        foreach($this->equipmentList as $key => $equipment){
            if($equipment['id'] == $id){
                $this->equipmentList[$key]['avaliable'] = false;


            }
        }

        $this->iteration ++;
        $this->equipment_id = null;
    }

    public function updatedEditingEndDateTime()
    {
        $this->getBookableEquipment();
        $this->iteration ++;
    }
}
