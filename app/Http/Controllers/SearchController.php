<?php

namespace App\Http\Controllers;
use App\Models\service;

use Illuminate\Http\Request;
use App\Models\Categoury;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
 /**
     * Search services by name or type.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Define custom error messages for validation
        $messages = [
            'search_query.required' => 'The search query is required.',
            'search_query.string' => 'The search query must be a string.',
        ];

        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'search_query' => 'required|string',
        ], $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Retrieve the search query from the request
        $searchQuery = $request->input('search_query');

        // Start building the query to search for services
        $services = service::query();

        // Apply search query if provided
        if ($searchQuery) {
            $services->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('type', 'like', '%' . $searchQuery . '%');
            });
        }

        // Retrieve search results
        $searchResults = $services->get();

        // Return the search results as JSON response
        return response()->json($searchResults);
    }
 /**
     * Filter services by category, location, and price.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        // Define custom error messages for validation
        $messages = [
            'categoury_id.exists' => 'The selected categoury does not exist.',
            'location.string' => 'The location must be a string.',
            'min_price.numeric' => 'The minimum price must be a number.',
            'max_price.numeric' => 'The maximum price must be a number.',
        ];

        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'categoury_id' => 'required|exists:categouries,id',
            'location' => 'nullable|string',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
        ], $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Retrieve the filtering parameters from the request
        $categouryId = $request->input('categoury_id');
        $location = $request->input('location');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        // Start building the query to filter services
        $services = service::where('categoury_id', $categouryId);

        // Apply location filter if provided
        if ($location) {
            $services->where('location', 'like', '%' . $location . '%');
        }

        // Apply price filters if provided
        if ($minPrice) {
            $services->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $services->where('price', '<=', $maxPrice);
        }

        // Retrieve the filtered services
        $filteredServices = $services->get();
         // Check if the filtered services collection is empty
         if ($filteredServices->isEmpty()) {
            return response()->json(['message' => 'No services found .'], 200);
        }

        // Return the filtered services as JSON response
        return response()->json($filteredServices);
    }
}
