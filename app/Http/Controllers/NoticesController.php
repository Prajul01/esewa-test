<?php

namespace App\Http\Controllers;

use App\Http\Controllers\backend\BackendBaseController;
use App\Models\notices;
use Illuminate\Http\Request;

class NoticesController extends BackendBaseController
{
    protected $route ='admin.notice.';
    protected $panel ='Notice';
    protected $view ='backend.notice.';
    protected $title;
    protected $model;
    function __construct(){
        $this->model = new notices();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function showAll(Request $request){
        $data = notices::all();
        return $data;
    }
    public function index()
    {
        $this->title = 'List';
        $data['row'] = $this->model->get();
//        $this->panel;
//        $data['rows'] = notices::all();
//        $data['rows'] = ->get();
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

        return view($this->__loadDataToView($this->view . 'create'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function uploadimage(Request $request)
    {
        $image = $request->file('upload');
        $imageData = base64_encode(file_get_contents($image->getRealPath()));
        $url = 'data:'.$image->getClientMimeType().';base64,'.$imageData;
        return response()->json(['fileName' => $image->getClientOriginalName(), 'uploaded' => 1, 'url' => $url]);
    }
    public function store(Request $request)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            "to" => "/topics/all",
            "priority" => "high", 
            'notification' => [
                'title' => $request->title,
                'body' => $request->short_description,
            ],
        ];
        $fields = json_encode ($data);
        $headers = array (
            'Authorization: key=' . "AAAAMZvPU2s:APA91bEY5GlYz7Ok21J5z4x1Ph0wmZ3DX4PuKupWURog2yfp8J6s8_VS9e_iPfOQQYxW6vjrxVPnvDtTBToIQ6w_KBDsqLfOwM1PEOetXLuYV4JacTehVPyT7BXiHhNfUca0E6OLVIg2",
            'Content-Type: application/json'
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        $result = curl_exec ( $ch );
        //var_dump($result);
        curl_close ( $ch );
        $data['row']=$this->model->create($request->all());

        if ($data['row']){
            request()->session()->flash('success',$this->panel . 'Created Successfully');
        }else{
            request()->session()->flash('error',$this->panel . 'Creation Failed');
        }
//        return redirect()->route('category.index',compact('data'));
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
        return redirect()->route($this->__loadDataToView($this->route . 'index'))->with('success','Data Deleted Successfully');
    }
}


