<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = ['Internal', 'Enterprise', 'Public'];
        
        return response()->json($tags);
    }
}
