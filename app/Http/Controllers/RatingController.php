<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\Favourite;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Sponsor $sponsor)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);
        $client = auth()->user()->userable;
        $rate = Rating::where('client_id', $client->id)
            ->where('sponsor_id', $sponsor->id)
            ->first();
        if ($rate != null) {
            return response()->json([
                'error' => 'you cannot rate the same person again'
            ]);
        }
        $rate = Rating::create([
            'client_id' => $client->id,
            'sponsor_id' => $sponsor->id,
            'rating' => $request->rating
        ]);


        return response()->json([
            'message' => 'Thank you for rating',
            'rate' => $rate->rating
        ], 200);
    }

    public function sponserRate(Sponsor $sponsor) {
        // Get the rates from the pivot table
        $rates = $sponsor->clients()->get()->pluck('pivot.rating');

        // Calculate the average rate within the range of 1 to 5
        $averageRate = $rates->filter(function ($rate) {
            return $rate >= 1 && $rate <= 5;
        })->avg();
        return $averageRate;
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
