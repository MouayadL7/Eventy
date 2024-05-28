<?php

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerification;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\RegisterSponsorRequest;
use App\Jobs\DeleteAccount;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    public function register(Request $request) :JsonResponse
    {
        $input = $request->all();
        if ($input['is_client'])
        {
            $registerRequest = new RegisterClientRequest();
            $validator = Validator::make($input, $registerRequest->rules());

            if ($validator->fails())
            {
                return $this->sendError($validator->errors());
            }

            $input['password'] = Hash::make($input['password']);
            $input['role_id']  = Role::ROLE_CLIENT ;

            $user = User::create($input);

            $input['image'] = $this->getImage($request, "client");

            $response = $this->extracted_data($user, Client::create([
                'first_name'    =>  $input['first_name'],
                'last_name'     =>  $input['last_name'],
                'address'       =>  $input['address'],
                'gender'        =>  $input['gender'],
                'image'         =>  $input['image'],
            ]));
        }
        else
        {
            $registerRequest = new RegisterSponsorRequest();
            $validator = Validator::make($input, $registerRequest->rules());

            if ($validator->fails())
            {
                return $this->sendError($validator->errors());
            }

            $input['password'] = Hash::make($input['password']);
            $input['role_id']  = $input['is_sponsor'] = Role::ROLE_SPONSOR;

            $user = User::create($input);

            $input['image'] = $this->getImage($request, "sponsor");

            $response = $this->extracted_data($user, Sponsor::create([
                'first_name'     =>  $input['first_name'],
                'last_name'      =>  $input['last_name'],
                'image'          =>  $input['image'],
                'work_experience'=>  $input['work_experience'],

            ]));
        }

        return $response;
    }

    public function getImage($request, $type)
    {
        $user_image = "";
        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $user_image = time().'.'.$image->getClientOriginalExtension();
            $path = 'images/' . $type.'/';
            $image->move($path, $user_image);
            $user_image = $path.$user_image;
        }

        return $user_image;
    }

    protected function extracted_data($user, $specified_user_data): JsonResponse
    {
        // $the second parameter can be client or sponsor
        $user->userable()->associate($specified_user_data);
        $user->save();

        // just to send it to the API
        $token = $user->createToken('Personal Access Token')->accessToken;

        $specified_user_data['phone'] = $user['phone'];
        $specified_user_data['email'] = $user['email'];
        $specified_user_data['role_id'] = $user['role_id'];
        $specified_user_data['accessToken'] = $user->createToken('access token')->plainTextToken;;
        $specified_user_data['id'] = $user['id'];

        // just to create Budget
        Budget::create([
            'user_id' => $user->id,
        ]);

        // just to send email verification
        event(new EmailVerification($user->email));
        DeleteAccount::dispatch($user)->delay(60);

        return $this->sendResponse($specified_user_data);
    }
}


