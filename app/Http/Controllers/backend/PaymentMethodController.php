<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends BackendBaseController
{
    protected $route ='admin.payment-methods.';
    protected $panel ='Payment-Methods';
    protected $view ='backend.paymentmethod.';
    protected $title;
    protected $model;
    function __construct(){
        $this->model = new PaymentMethod();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->title = 'List';
        $data['row'] = $this->model->get();
//        $this->panel;
//        $data['rows'] = notices::all();
//        $data['rows'] = ->get();
        return view($this->__loadDataToView($this->view . 'index'),compact('data'));
    }
    public function showAll(Request $request)
    {
        $data = $this->model::where('status',1)->get();
        return PaymentMethodResource::collection($data);

//        $response = new PaymentMethodResource($data);
//        return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->title = 'Create';
        return view($this->__loadDataToView($this->view . 'create'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $image = $request->file('payment_image');
        if ($image) {
            $image_name = rand(6785, 9814) . '_' . $image->getClientOriginalName();

            $image->move(public_path('uploads/images/payments/images/'), $image_name);

            // Rename the field for database storage
            $request->merge(['image' => $image_name]);

            // Store the image file name in the model
            $this->model->fill($request->all());
            $this->model->save();
        }

        $data['row'] = $this->model->create($request->except('payment_image'));

        if ($data['row']){
            request()->session()->flash('success',$this->panel . 'Created Successfully');
        }else{
            request()->session()->flash('error',$this->panel . 'Creation Failed');
        }
        return redirect()->route($this->__loadDataToView($this->route . 'index'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $this->title= 'View';
        $data['row']=$this->model->findOrFail($id);
        return view($this->__loadDataToView($this->view . 'view'),compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    { $this->title= 'Edit';
        $data['row']=$this->model->findOrFail($id);
        return view($this->__loadDataToView($this->view . 'edit'),compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
//        $request->request->add(['updated_by' => auth()->user()->id]);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $this->model->findorfail($id)->delete();
        return redirect()->route($this->__loadDataToView($this->route . 'index'))->with('success',$this->panel .' Deleted Successfully');
    }


    public function updateStatus(Request $request)
    {
        $catId = $request->input('id');
        $status = $request->input('status');

        // Retrieve the record from the database
        $category = $this->model::find($catId);

        if (!$category) {
            $request->session()->flash('error', 'Category not found');
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Check if the model is retrieved successfully before attempting to update
        if ($category) {
            $category->update(['status' => $status]);

            // Check if the update was successful
            if ($category->wasChanged('status')) {
                $request->session()->flash('success', 'Status updated successfully');
            } else {
                $request->session()->flash('error', $this->panel . ' Update failed');
            }

            return response()->json(['success' => 'Status updated successfully'], 200);
        }
    }

//    public function updateStatus(Request $request)
//    {
//        $catId = $request->input('id');
//        $status = $request->input('status');
//
//        // Retrieve the record from the database
//        $category = $this->model::find($catId);
//
//        if (!$category) {
//            $request->session()->flash('error', 'Category not found');
//            return response()->json(['error' => 'Category not found'], 404);
//        }
//
//        $category->update(['status' => $status]);
//
//        // Check if the update was successful
//        if ($category->wasChanged('status')) {
//            $request->session()->flash('success', 'Status updated successfully!!!!!!!!');
//        } else {
//            $request->session()->flash('error', $this->panel . ' Update failed');
//        }
//
//
//        return response()->json(['success' => 'Status updated successfullyhhhhhhh'], 200);
//    }

    public function changeStatusproduct(Request $request)
    {
        $slider = Product::find($request->id);
        $slider->status = $request->status;
        $slider->save();

        return response()->json(['success'=>'Status change successfully.']);



    }



}
