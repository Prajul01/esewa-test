<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\notices;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class APIController extends Controller
{
    function notice(){
        return notices::all();
    }
    function notice_save(Request $req){
        $date = date('Y-m-d h:i:s');
        $user = new notices();
        $user->title = $req->input('title');
        $user->description = $req->input('description');
        $user->short_description = $req->input('short_description');

        $user->created_at = $date;
        $user->updated_at = $date;






        $resp = [
            'success' => false,
            'message' => 'Save failed'
        ];

        if($user->save()){
            $resp['success'] = true;
            $resp['message'] = 'notice  saved';

        }else{

        }

        return response()->json($resp);
//        return notices::create($request->all());
    }
//
//    function product(){
//        return Product::where('status',1)->get();
//    }

    public function login(Request $request){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = User::where('id',auth()->user()->id)->first();
            $success['token'] =  $user->createToken('TokenName')-> accessToken;
            return $success;
            $success['user'] = $user;
           $response = [
                'success' => "true",
                'status' => $this->successStatus,
                'data'  => $success
            ];
            return response()->json($response, $this-> successStatus);
        } else {
            return "Failed";
            $response = [
                'success' => "false",
                'status' => $this->errorStatus
            ];
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


}
