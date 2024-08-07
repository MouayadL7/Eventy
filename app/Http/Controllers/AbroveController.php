<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Mail\AbroveEmail;
use App\Models\Abrove;
use App\Models\Categoury;
use App\Models\Service;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AbroveController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $abroves = DB::table('abroves')
                    ->join('users', 'abroves.sponsor_id', '=', 'users.id')
                    ->whereRaw('users.email_verified = ?', [1])
                    ->join('sponsors', 'users.userable_id', '=', 'sponsors.id')
                    ->select('abroves.id', 'first_name', 'last_name', 'email', 'phone', 'work_experience', 'location', 'price', 'image', 'abroves.created_at as date')
                    ->get();
        return $this->sendResponse($abroves);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $abrove = Abrove::with('sponsor.userable')->find($id);
        return $this->sendResponse($abrove);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Abrove $abrove)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Abrove $abrove)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Abrove $abrove)
    {
        //
    }

    public function reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'response' => ['required', 'in:accept,cancel'],
            'abrove_id' => ['required', 'exists:abroves,id']
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $abrove = Abrove::find($request->abrove_id);
        $sponsor = User::find($abrove->sponsor_id);
        if (is_null($sponsor)) {
            return $this->sendResponse();
        }
        if ($request->response == 'accept') {
            $service = Service::create([
                'name' => $sponsor->userable->first_name . ' ' . $sponsor->userable->last_name,
                'type' => 'sponsor',
                'categoury_id' => Categoury::CATEGOURY_ORGANIZER,
                'contact_number' => $sponsor->phone,
                'rating' => 0,
                'location' => $sponsor->userable->location,
                'profile_image' => $sponsor->userable->image,
                'price' => $abrove->price,
                'description' => $sponsor->userable->work_experience
            ]);
            $sponsor->userable->update([
                'service_id' => $service->id
            ]);
            $data = [
                'title' => 'Accept Sponsor',
                'body'  => 'You have been successfully accepted into our application'
            ];
            Mail::to($sponsor->email)->send(new AbroveEmail($data));
        }
        else {
            $sponsor->delete();
        }
        $abrove->delete();
        return $this->sendResponse();
    }
}
