<?php

namespace App\Http\Controllers;
use App\Models\service;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\Categoury;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class Servicecontroller extends BaseController
{
    public function addservice(Request $request)
    { 
        $data = $request->all();
        $validator = Validator::make($data, [
            'name'           => 'required|string',
            'categoury_id'   => 'required|integer',
            'type'           => 'required|string',
            'contact_number' => 'required|string',
            'rating'         => 'required|numeric',
            'price'          => 'required|numeric',
            'location'       => 'required|string',               
            'description'    => 'required|string',
            'image'          => ['image', 'mimes:jpeg,png,bmp,jpg,gif,svg'],     // Assuming profile_photo is an image field
        ]);

        if ($validator->fails())
        {
            return $this->sendError($validator->errors());
        }
    
            $serv_image = null;
            if($request->hasFile('image'))
            {
                $image= $request->file('image');
                $serv_image = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('image'),$serv_image);
                $serv_image = 'image/'.$serv_image ;
            }
            $data['image'] = $serv_image;
    
            Service::create($data);

        return response()->json([
            'message' => 'the service added  succefully..'
        ]);

    }

    public function getallservices()
    {
        $allservices= service::all();
        return response()->json([
       'message' => 'Retrieved successfully',
       'data'    => $allservices,
        ],200);
    }

    public function showcategouryser(categoury $categoury)
    {
        
        $services = $categoury->services;
        return response()->json([
            'services' => $services
        ], 200);
    }
}
