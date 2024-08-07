<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function get_profile()
    {
        $data = auth()->user()->userable->get_info();
        return  $this->sendResponse($data);
    }
}
