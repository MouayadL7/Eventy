<?php

namespace App\Http\Controllers\Auth;

use App\Events\EmailVerification;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\RegisterSponsorRequest;
use App\Jobs\DeleteAccount;
use App\Models\Abrove;
use App\Models\Budget;
use App\Models\Cart;
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
    /**
     * to register the user in app
     */
    public function register(Request $request) :JsonResponse
    {
        $input = $request->all();
        if ($input['is_client'])
        {
            // Validate the data
            $validator = Validator::make($input, (new RegisterClientRequest())->rules());
            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            // Hashing password
            $input['password'] = Hash::make($input['password']);

            // Assign role_id to the client
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

            // Create a cart for the client
            $cart = new Cart();
            $cart->client_id = Client::get()->last()->id;
            $cart->save();
        }
        else
        {
            // validate the data
            $validator = Validator::make($input, (new RegisterSponsorRequest())->rules());
            if ($validator->fails()) {
                return $this->sendError($validator->errors());
            }

            // Hashing password
            $input['password'] = Hash::make($input['password']);

            // Assign role_id to the sponsor
            $input['role_id']  = $input['is_sponsor'] = Role::ROLE_SPONSOR;

            $user = User::create($input);

            $input['image'] = $this->getImage($request, "sponsor");

            $response = $this->extracted_data($user, Sponsor::create([
                'first_name'      => $input['first_name'],
                'last_name'       => $input['last_name'],
                'image'           => $input['image'],
                'work_experience' => $input['work_experience'],
                'location'        => $input['location'],

            ]));

            // In order for the admin to approve it in the application
            Abrove::create([
                'sponsor_id' => $response->getData()->data->id,
                'price'      => $request->price
            ]);
        }
        return $response;
    }

    /**
     * to store image in files, and get the path.
     */
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

        $specified_user_data['phone'] = $user['phone'];
        $specified_user_data['email'] = $user['email'];
        $specified_user_data['role_id'] = $user['role_id'];
        $specified_user_data['id'] = $user['id'];

        // create Budget
        Budget::create([
            'user_id' => $user->id,
        ]);

        // send email verification
        event(new EmailVerification($user->email));

        // to delete user's account after one day if is not verified
        DeleteAccount::dispatch($user)->delay(84600);

        return $this->sendResponse($specified_user_data);
    }
}


