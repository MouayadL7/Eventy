<?php

namespace App\Http\Controllers;

use App\Models\Categoury;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class CategouryController extends BaseController
{
    public function index() {
        $categories = Categoury::all();
        return $this->sendResponse($categories);
    }
    public function show($id) {
        if ($id == Categoury::CATEGOURY_ORGANIZER) {
            $categoury = Categoury::with(['services.images', 'services.sponsor'])->find($id);
        }
        else {
            $categoury = Categoury::with('services.images')->find($id);
        }
        return $this->sendResponse($categoury);
    }
}

