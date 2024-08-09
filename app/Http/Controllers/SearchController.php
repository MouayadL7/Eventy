<?php

namespace App\Http\Controllers;
use App\Models\service;

use Illuminate\Http\Request;
use App\Models\Categoury;
use Illuminate\Support\Facades\Validator;

class SearchController extends BaseController
{
 /**
     * Search services by name or type.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // Validate the search parameters
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        // Build the search query
        $query = Service::query();

        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }

        if (!empty($request['type'])) {
            $query->where('type', 'like', '%' . $request['type'] . '%');
        }

        $services = $query->get();

        // Check if services are found
        if ($services->isEmpty()) {
            return $this->sendError('No services found matching the criteria.');
        }

        return $this->sendResponse($services);
    }

    /**
     * Filter services by category and price range.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        // Validate the filter parameters
        $validator = Validator::make($request->all(),[
            'categoury_id' => 'nullable|integer',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
        ], [
            'category_id.integer' => 'The category ID must be an integer.',
            'min_price.numeric' => 'The minimum price must be a numeric value.',
            'max_price.numeric' => 'The maximum price must be a numeric value.',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        // Build the filter query
        $query = Service::query();

        if (!empty($request['categoury_id'])) {
            $query->where('categoury_id', $request['categoury_id']);
        }

        if (isset($request['min_price']) && isset($request['max_price'])) {
            $query->whereBetween('price', [$request['min_price'], $request['max_price']]);
        }

        $services = $query->get();

        // Check if services are found
        if ($services->isEmpty()) {
            return $this->sendError('No services found for the selected category and price range.');
        }

        return $this->sendResponse($services);
    }
    
}
