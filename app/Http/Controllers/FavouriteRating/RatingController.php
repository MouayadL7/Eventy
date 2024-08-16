<?php

namespace App\Http\Controllers\FavouriteRating;

use App\Http\Controllers\BaseController;
use App\Models\Sponsor;
use App\Models\Favourite;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sponsor_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $client = auth()->user()->userable;
        $sponsor = User::find($request->sponsor_id)->userable;

        if ($client->hasRated($sponsor->id)) {
            return $this->sendError('You can not rate the same person again');
        }

        $rate = Rating::create([
            'client_id' => $client->id,
            'sponsor_id' => $sponsor->id,
            'rating' => $request->rating
        ]);

        $sponsor->service->update([
            'rating' => $sponsor->averageRating()
        ]);

        return $this->sponsorRate($request->sponsor_id);
    }

    public function sponsorRate($id) {
        $sponsor = User::find($id)->userable;
        return $this->sendResponse([
            'rating' => $sponsor->averageRating()
        ]);
    }

    public function myRates() {
        // Get the authenticated sponsor
        $sponsor = auth()->user()->userable;

        // Retrieve the rates from the pivot table
        $rates = $sponsor->clients()
                        ->get()
                        ->pluck('pivot.rating');

        // Calculate the average rate within the range of 1 to 5
        $averageRate = $rates->filter(function ($rate) {
            return $rate >= 1 && $rate <= 5;
        })->avg();

        // Pass the data to the view
        return response()->json([
            'my average rate is : ' => $averageRate
        ]);
    }
}
