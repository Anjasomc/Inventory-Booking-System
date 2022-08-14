<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Redirect;
use Response;
use App\Models\Loan;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetLoan;
use DataTables;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Populate data in table
        if($request->ajax()){
            $loans = Loan::latest()->where('status_id', '<', '4')->with('assets')->with('user')->get();

            return Datatables::of($loans)
                ->setRowId('id')
                ->editColumn('start_date_time', function($loan){
                    return Carbon::parse($loan->start_date_time)->format('d F Y G:i');
                })
                ->editColumn('end_date_time', function($loan){
                    return Carbon::parse($loan->end_date_time)->format('d F Y G:i');
                })
                ->editColumn('status_id', function($loan){
                    switch($loan->status_id){
                        case 0:
                            return '<span class="badge bg-success">Booked</span>';
                            break;
                        case 1:
                            return '<span class="badge bg-warning text-dark">Reservation</span>';
                            break;
                        case 2:
                            return '<span class="badge bg-danger">Overdue</span>';
                            break;
                    }
                })
                ->rawColumns(['status_id','action'])
                ->addColumn('action', function ($loan){
                    if($loan->status_id == 1){
                        return '<button class="bookOutLoan btn btn-info btn-sm rounded-0" type="button" data-toggle="tooltip" data-placement="top" title="Book out"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                        <button class="modifyLoan btn btn-warning btn-sm rounded-0" type="button" data-toggle="tooltip" data-placement="top" title="Modify"><i class="fa fa-pen-to-square"></i></button>
                        <button class="deleteLoan btn btn-danger btn-sm rounded-0" type="button" data-assetname="' . $loan->id . '" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fa fa-trash-can"></i></button>';
                    }else{
                        return '<button class="completeLoan btn btn-success btn-sm rounded-0" type="button" data-toggle="tooltip" data-placement="top" title="Complete"><i class="fa-solid fa-check"></i></button>
                        <button class="modifyLoan btn btn-warning btn-sm rounded-0" type="button" data-toggle="tooltip" data-placement="top" title="Modify"><i class="fa fa-pen-to-square"></i></button>';
                    }
                })
                ->make(true);
        }

        //Get list of users
        $users = User::latest()->get();

        //Render rest of the page
        return view('loan.loans',[
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Render rest of the page
        return view('loan.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //We now need to verify the equipment the user has booked is actually bookable still.
        //This can fail for two reasons
        //The user has modified the array used to store equipment they are allowed to book client side. We should never trust this data.
        //Another user has booked the equipment after this user had opened the create loan form but had not selected submit yet.
        if(!($validator->errors()->has('equipmentSelected'))){
            //Blade php works much better with an arrays rather than JSON so lets convert
            $equipmentArr = json_decode($request->input('equipmentSelected'),true);
            //For some reason the JSON is returning length as part of the array so lets remove
            unset($equipmentArr['length']);

            $newEquipmentArr = [];


            //This is used to re-populate the dropdown of assets that are avaliable to book
            //We need to make sure the start and end dates have passed validation before
            //fetching this information from the database
            if(!($validator->errors()->has('start_date')) and !($validator->errors()->has('end_date'))){
                //This gives us all the equipment avaliable to be booked between the two dates
                $bookableEquipment = $this->getBookableEquipment($request)->getData();

                //We then need to mark the equipment the user has booked so we can add it back to the
                //shopping cart and not to the equipment dropdown menu
                foreach($equipmentArr as $id => $value){
                    $idFound = false;
                    foreach($bookableEquipment as &$equipment){
                        if($id == $equipment->id){
                            //We have found the ID is list of bookable equipment
                            $idFound = true;
                            $equipment->selected = true;
                        }
                    }

                    if($idFound == false){
                        //User has tried to book assets that are no longer avaliable
                    }
                }

                //Merge the bookable equipment back into the oldInput array which is passed back to the view
                //This is used to re-populate both the equipment dropdown menu and the shopping cart table
                $request->merge(['bookableEquipment' => $bookableEquipment]);

                //dd($bookableEquipment);
            }

        }

        //Return any errors during the validation
        if($validator->fails()){
            $test = redirect('loans/create')
                        ->withErrors($validator)
                        ->withInput();

            return $test;
        }

        //Retrieve the validated input
        $validated = $validator->validated();

        $loanId = Loan::create([
            'user_id' => $validated['user_id'],
            'status_id' => $validated['status_id'] ?? "0",
            'start_date_time' => carbon::parse($validated['start_date']),
            'end_date_time' => carbon::parse($validated['end_date']),
            'details' => $validated['details'] ?? "",
        ])->id;

        $loan = Loan::find($loanId);

        $equipmentArr = json_decode($request->equipmentSelected,true);
        unset($equipmentArr['length']);

        //Add assets into asset_loan table
        $loan->assets()->sync($equipmentArr);

        return redirect()->route('loans.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $loan = Loan::with('assets')->with('user')->find($id);

        return view('loan.show',[
            'loan' => $loan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Get list of users
        $users = User::latest()->get();
        $loan = Loan::with('assets')->with('user')->find($id);

        //Render rest of the page
        return view('loan.edit',[
            'loan' => $loan,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $actualId = $id;

        //Perform the first step of validation on the data. At this stage we are
        //just checking that data has arrived in the expected format for further
        //processing afterwards
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer',
            'start_date' => 'required|date|before:end_date|nullable',
            'end_date' => 'required|date|after:start_date|nullable',
            'equipmentSelected' => 'required|json',
            'details' => 'nullable|string',
            'status_id' => 'required|string|in:0,1',
        ]);

        //We now need to verify the equipment the user has booked is actually bookable still.
        //This can fail for two reasons
        //The user has modified the array used to store equipment they are allowed to book client side. We should never trust this data.
        //Another user has booked the equipment after this user had opened the create loan form but had not selected submit yet.
        if(!($validator->errors()->has('equipmentSelected'))){
            //Blade php works much better with an arrays rather than JSON so lets convert
            $equipmentArr = json_decode($request->input('equipmentSelected'),true);
            //For some reason the JSON is returning length as part of the array so lets remove
            unset($equipmentArr['length']);

            $newEquipmentArr = [];


            //This is used to re-populate the dropdown of assets that are avaliable to book
            //We need to make sure the start and end dates have passed validation before
            //fetching this information from the database
            if(!($validator->errors()->has('start_date')) and !($validator->errors()->has('end_date'))){
                //This gives us all the equipment avaliable to be booked between the two dates
                $bookableEquipment = $this->getBookableEquipment($request)->getData();

                //We then need to mark the equipment the user has booked so we can add it back to the
                //shopping cart and not to the equipment dropdown menu
                foreach($equipmentArr as $id => $value){
                    $idFound = false;
                    foreach($bookableEquipment as &$equipment){
                        if($id == $equipment->id){
                            //We have found the ID is list of bookable equipment
                            $idFound = true;
                            $equipment->selected = true;
                        }
                    }

                    if($idFound == false){
                        //User has tried to book assets that are no longer avaliable
                    }
                }

                //Merge the bookable equipment back into the oldInput array which is passed back to the view
                //This is used to re-populate both the equipment dropdown menu and the shopping cart table
                $request->merge(['bookableEquipment' => $bookableEquipment]);

                //dd($bookableEquipment);
            }

        }

        //Return any errors during the validation
        if($validator->fails()){
            $test = redirect('loans/create')
                        ->withErrors($validator)
                        ->withInput();

            return $test;
        }

        //Retrieve the validated input
        $validated = $validator->validated();

        Loan::where('id',$actualId)->update([
            'user_id' => $validated['user_id'],
            'status_id' => $validated['status_id'] ?? "0",
            'start_date_time' => carbon::parse($validated['start_date']),
            'end_date_time' => carbon::parse($validated['end_date']),
            'details' => $validated['details'] ?? "",
        ]);

        //dd($request->equipmentSelected);

        $loan = Loan::with('assets')->find($actualId);

        $equipmentArr = json_decode($request->equipmentSelected,true);

        unset($equipmentArr['length']);

        //dd($equipmentArr);

        //Add assets into asset_loan table
        $loan->assets()->sync($equipmentArr);

        return redirect()->route('loans.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $loan = Loan::find($id);

        $loan->delete();

        return Response::json($loan);
    }

    /**
     * Mark booking as completed
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function completeBooking(Request $request, $id)
    {
        $loan = Loan::where('id', $request->id)->update([
            'status_id' => 4
        ]);

        return Response::json(Loan::find($id));
    }

    /**
     * Change booking from a reservation to booked out
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bookOutBooking(Request $request, $id)
    {
        $loan = Loan::where('id', $request->id)->update([
            'status_id' => 0
        ]);

        return Response::json(Loan::find($id));
    }
}
