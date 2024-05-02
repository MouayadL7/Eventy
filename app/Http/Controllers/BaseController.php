<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller 
{
    public function sendResponse($result = [], $data_name = "data")
    {
        $response = [
            'status' => 'success',
            $data_name => $result
        ];

        return response()->json($response, 200);
    }

    public function sendError($errorMessage = [], $code = 200)
    {
        $response = [
            'status' => 'failure'
        ];

        if(!empty($errorMessage))
        {
            $response['error_message'] = $errorMessage;
        }

        return response()->json($response, $code);
    }
}
