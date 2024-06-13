<?php

namespace App\Http\Controllers;

use App\Models\Categoury;

use Illuminate\Http\Request;

class CategouryController extends Controller
{
    public function index() {
        $categories = Categoury::all();
        return $categories;
    }
    public function show(Categoury $categoury) {
        return $categoury->load('sponsors', 'services');
    }
}

