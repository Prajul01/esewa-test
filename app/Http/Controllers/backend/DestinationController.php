<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Models\categories;
use App\Models\destinations;
use App\Models\destinations_images;
use Illuminate\Http\Request;

class DestinationController extends BackendBaseController
{
    protected $route ='admin.destination.';
    protected $panel ='Destination';
    protected $view ='backend.destination.';
    protected $title;
    protected $model;
    function __construct(){
        $this->model = new destinations();
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
        $data['images'] = destinations_images::get();
        return view($this->__loadDataToView($this->view . 'index'),compact('data'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->title = 'Create';
        $data['categories'] = categories::pluck('title', 'id');
        return view($this->__loadDataToView($this->view . 'create'),compact('data'));
    }


    //showByCategory
    public function showByCategory($id){
        $data = destinations::where('category_id',$id)->with('Categories')->with('Images')->get();
        return $data;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(DestinationRequest $request)
    {
        $data['row']=$this->model->create($request->all());
        //for multiple image upload
        $imageFiles = $request->file('product_image');
        $imageArray['destination_id'] = $data['row']->id;
        for ($i = 0; $i < count($imageFiles); $i++){
            $image      = $imageFiles[$i];
            $image_name = rand(6785, 9814).'_'.$image->getClientOriginalName();
             $image->move(public_path('uploads/images/destinations/images/'), $image_name);
            $imageArray['image'] = $image_name;
            destinations_images::create($imageArray);
        }
        if ($data['row']){
            request()->session()->flash('success',$this->panel . 'Created Successfully');
        }else{
            request()->session()->flash('error',$this->panel . 'Creation Failed');
        }
        return redirect()->route($this->__loadDataToView($this->route . 'index'));

    }
    public function showAll(){
        $data = destinations::all();
        return $data;
    }
    public function imageShowAll($id){
        $data = destinations_images::where('destination_id',$id)->get();
        return $data;
    }
      public function show($id)
    {

        $this->title= 'View';
        $data['row']=$this->model->findOrFail($id);
        $data['images']=destinations_images::get();
//        dd($data['row']);
        return view($this->__loadDataToView($this->view . 'view'),compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   $this->title= 'Edit';
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

}
