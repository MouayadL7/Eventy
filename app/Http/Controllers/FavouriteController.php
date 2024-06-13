<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
 use App\Models\Favourite;
 use Illuminate\Http\Request;

class FavouriteController extends BaseController
{
    public function add(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $favorite = new Favourite();
        $favorite->user_id = auth()->id();
        $favorite->service_id = $request->service_id;
        $favorite->save();

        return $this->sendResponse([], 'Service added to favorites successfully');
    }

    // Remove a service from favorites
    public function remove(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:favourites,service_id',
        ]);

        Favourite::where('user_id', auth()->id())
                ->where('service_id', $request->service_id)
                ->delete();

                return $this->sendResponse([], 'Service added to favorites successfully');
    }

    // List favorite services for the authenticated client

    public function list()
    {
        $favorites = Favourite::where('user_id', auth()->id())
        ->with(['service' => function($query) {
            $query->select('id', 'type', 'name');
        }])
        ->get();

        return response()->json($favorites, 200);
    }
    // List favorite services info for the authenticated client
    public function list_info()
    {
        $favorites = Favourite::where('user_id', auth()->id())
                             ->with('service')
                             ->get();

        return response()->json($favorites, 200);
    }




}


