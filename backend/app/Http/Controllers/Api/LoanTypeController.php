<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanType;

class LoanTypeController extends Controller
{
    public function index()
    {
        $types = LoanType::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
