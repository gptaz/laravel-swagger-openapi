<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;

class MainController extends Controller
{
    //

    public function index()
    {
        
        return view('items');
    }
    
}
