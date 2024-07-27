<?php

namespace App\Http\Controllers;
use App\Models\service;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Categoury;
use App\Models\ServiceImage;
use App\Models\Booking;
use Illuminate\Support\Facades\Validator;



class ServiceController extends BaseController
 {
    public function addservice(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'categoury_id' => 'required|integer',
            'type' => 'required|string',
            'contact_number' => 'required|string',
            'rating' => 'required|numeric',
            'price' => 'required|numeric',
            'location' => 'required|string',
            'description' => 'required|string',
            'profile_image' => 'required|image|mimes:jpeg,png,bmp,jpg,gif,svg',
            'images.*' => 'image|mimes:jpeg,png,bmp,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        // Handle profile image upload
        $profileImage = null;
        if ($request->hasFile('profile_image')) {
            $profileImage = time() . '_profile.' . $request->profile_image->getClientOriginalExtension();
            $request->profile_image->move(public_path('images'), $profileImage);
            $profileImage = 'images/' . $profileImage;
        }
        $data['profile_image'] = $profileImage;

        // Create service
        $service = Service::create($data);


        // Handle multiple images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                ServiceImage::create([
                    'service_id' => $service->id,
                    'image_path' => 'images/' . $imageName,
                ]);
            }
        }


        return response()->json([
            'message' => 'The service added successfully.'
        ]);
    }

    public function getallservices()
    {
        $allServices = Service::with('images')->get();
        return response()->json([
            'message' => 'Retrieved successfully',
            'data' =>  $allServices,
        ], 200);
    }

    public function getservicebyid($service_id)
    {
        $service = Service::with('images')->find($service_id);

        if (!$service) {
            return $this->sendError('There is no service with this ID');
        }

        $serviceDetails = $service->toArray();
        $serviceDetails['images'] = $service->images->pluck('image_path');

        if ($service->categoury_id == Categoury::CATEGOURY_ORGANIZER) {
            $serviceDetails['sponsor'] = $service->sponsor;
        }

        return $this->sendResponse($serviceDetails);
    }
    public function showcategouryser(Categoury $categoury)
    {
        $services = $categoury->services;
        return response()->json([
            'services' => $services
        ], 200);
    }


}
