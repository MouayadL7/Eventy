<?php

namespace App\Http\Controllers\FavouriteRating;

use App\Http\Controllers\BaseController;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends BaseController
{
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => ['required', 'exists:services,id']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if (auth()->user()->userable->hasFav($request->service_id)) {
            return $this->sendError('This service already favourited');
        }

        $favourite = Favourite::create([
            'client_id' => auth()->user()->userable_id,
            'service_id' => $request->service_id
        ]);

        return $this->sendResponse($favourite);
    }

    // Remove a service from favorites
    public function remove($id)
    {
        $favourite = Favourite::find($id);
        if (is_null($favourite)) {
            return $this->sendError('There is no favourite with this ID');
        }

        $favourite->delete();
        return $this->sendResponse();
    }

    // List favorite services for the authenticated client

    public function list()
    {
        $favourites = Favourite::where('client_id', auth()->user()->userable_id)
            ->with(['service' => function($query) {
                $query->select('id', 'type', 'name', 'profile_image');
            }])
            ->get();

        $favourites = (new Favourite)->get_list($favourites);
        return $this->sendResponse($favourites);
    }
}


