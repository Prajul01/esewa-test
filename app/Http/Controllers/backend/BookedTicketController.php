<?php

namespace App\Http\Controllers\backend;

//use App\Enum\ActionEnum;
use App\Http\Controllers\Controller;
use App\Models\BookedTicket;
use App\Models\destinations;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\PaymentService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;


class BookedTicketController extends BackendBaseController
{
    protected $route ='admin.booked_tickets.';
    protected $panel ='Booked-Ticket';
    protected $view ='backend.booked_tickets.';
    protected $title;
    protected $model;
    function __construct(){
        $this->model = new BookedTicket();
    }

    public function index()
    {
        $this->title = 'List';
        $data['user']=User::all();
        $data['row'] = $this->model->get();
        return view($this->__loadDataToView($this->view . 'index'),compact('data'));
    }

    public function create()
    {
        $this->title = 'Create';
        return view($this->__loadDataToView($this->view . 'create'));
    }

    public function store(Request $request)
    {
        $destination = destinations::find($request->input('destination_id'));
        if (!$destination) {
            return response()->json('Destination not found', 404);
        }
        $ticketCount = $request->input('ticket_count');
        if ($destination->ticket_quantity >= $ticketCount) {
            $data['row'] = $this->model->create([
                'user_id'=>$request->user_id,
                'destination_id'=>$request->destination_id,
                'ticket_count'=>$request->ticket_count,
                'status' => 'pending',
            ]);
            if ($data['row']) {
                $destination->ticket_quantity -= $ticketCount;
                $destination->save();
                return response()->json($data, 200);
            } else {
                return response()->json('Creation Failed', 500);
            }
        } else {
            return response()->json('Not enough tickets available', 400);
        }
    }

    public function payment(Request $request)
    {
        $ticket_details['ticket']=BookedTicket::find($request->booking_id);
        $ticket_details['payment']=PaymentMethod::find($request->payment_id);
        $paymentService = new PaymentService();
                    $response = $paymentService->esewaPayment($ticket_details);
                    dd($response);
        }




    public function show($id)
    {

        $this->title= 'View';
        $data['row']=$this->model->findOrFail($id);
        $data['images']=destinations_images::get();
        return view($this->__loadDataToView($this->view . 'view'),compact('data'));
    }

    public function edit($id)
    {   $this->title= 'Edit';
        $data['row']=$this->model->findOrFail($id);
        return view($this->__loadDataToView($this->view . 'edit'),compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data['row'] =$this->model->findOrFail($id);
        if(!$data ['row']){
            request()->session()->flash('error','Invalid Request');
            return redirect()->route($this->__loadDataToView($this->route . 'index'));
        }
        if ($data['row']->update($request->all())) {
            $request->session()->flash('success', $this->panel .' Update Successfully');
        } else {
            $request->session()->flash('error', $this->panel .' Update failed');

        }
        return redirect()->route($this->__loadDataToView($this->route . 'index'));

    }

    public function destroy($id)
    {

        $this->model->findorfail($id)->delete();

        return redirect()->route($this->__loadDataToView($this->route . 'index'))->with('success',$this->panel .' Deleted Successfully');
    }

}
