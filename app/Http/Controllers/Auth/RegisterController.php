<?php

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerification;

use App\Http\Controllers\BaseController;
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
            $validator = Validator::make($input, [
                'first_name'    => 'required|string|max:255',
                'last_name'     => 'required|string|max:255',
                'email'         => 'required|email|unique:users,email',
                'phone'         => 'required|digits:10|unique:users,phone',
                'password'      => 'required|string|min:8',
                'address'       => 'required',
                'gender'        => 'required|in:male,female',
                'image'         => ['image', 'mimes:jpeg,png,bmp,jpg,gif,svg']
            ],[
                'phone.unique'  => 'phone is not unique',
                'phone.digits'  => 'Phone must contain number only',
                'email.unique'  => 'email is not unique',
                'password.min'  =>'password must be at least 8 characters'
             ]);

            if ($validator->fails())
            {
                return $this->sendError($validator->errors());
            }

            $input['password'] = Hash::make($input['password']);
            $input['role_id']  = Role::ROLE_CLIENT ;

            $user = User::create($input);

            $user_image = "";
            if($request->hasFile('image'))
            {
                $image= $request->file('image');
                $user_image = time().'.'.$image->getClientOriginalExtension();
                $path = 'images/' . 'client'.'/';
                $image->move($path, $user_image);
                $user_image = $path.$user_image ;
            }
            $input['image'] = $user_image;

            
            $response = $this->extracted_data($user, Client::create([
                'first_name'    =>  $input['first_name'],
                'last_name'     =>  $input['last_name'],
                'address'       => $input['address'],
                'gender'        => $input['gender'],
                'image'         => $input['image'],
            ]));
        }
        else
        {
            $validator = Validator::make($input, [
                'first_name'      => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'email'           => 'required|email|unique:users,email',
                'phone'           => 'required|digits:10|unique:users,phone',
                'password'        => 'required|string|min:8',
                'work_experience' => 'required',
                'image'           => ['image', 'mimes:jpeg,png,bmp,jpg,gif,svg']
            ],[
                'phone.unique'    => 'phone is not unique',
                'phone.digits'    => 'Phone must contain number only',
                'email.unique'    => 'email is not unique',
                'password.min'    =>'password must be at least 8 characters'
            ]);
    
            if ($validator->fails())
            {
                return $this->sendError($validator->errors());
            }
    
            $input['password'] = Hash::make($input['password']);
            $input['role_id']  = $input['is_sponsor'] = Role::ROLE_SPONSOR;
    
            $user = User::create($input);
    
            $user_image = null;
            if($request->hasFile('image'))
            {
                $image= $request->file('image');
                $user_image = time().'.'.$image->getClientOriginalExtension();
                $path = 'images/' . 'sponsor'.'/';  
                $image->move($path, $user_image);
                $user_image = $path.$user_image ;
            }
            $input['image'] = $user_image;
    
            
            
            $response = $this->extracted_data($user, Sponsor::create([
                'first_name'     =>  $input['first_name'],
                'last_name'      =>  $input['last_name'],
                'image'          => $input['image'],
                'work_experience'=>$input['work_experience'],
                    
            ]));
        }
        return $response;
    }
    
    protected function extracted_data($user, $specified_user_data): JsonResponse
    {
        //$the second parameter can be clienrt or sponsor
        $user->userable()->associate($specified_user_data);
        $user->save();

        // just to send it to the API
        $token = $user->createToken('Personal Access Token')->accessToken;

        $specified_user_data['phone'] = $user['phone'];
        $specified_user_data['email'] = $user['email'];
        $specified_user_data['role_id'] = $user['role_id'];
        $specified_user_data['accessToken'] = $user->createToken('access token')->plainTextToken;;
        $specified_user_data['id'] = $user['id'];

        // just to send email verification
      //  event(new EmailVerification($user));

        return $this->sendResponse($specified_user_data);
    }
}


