<?php

namespace App\Http\Controllers;

class TagController extends Controller
{
    public function index()
    {
        $tags = ['Internal', 'Enterprise', 'Public'];

        return response()->json($tags);
    }
}
