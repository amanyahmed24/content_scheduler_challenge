<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    //all available platforms 

    public function index()
    {
        return response()->json([
            'data' => Platform::all()
        ]);
    }
}
