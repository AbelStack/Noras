<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        $dbConnected = false;
        
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }
        
        return response()->json([
            'status' => 'ok',
            'message' => 'Application is running',
            'database' => $dbConnected ? 'connected' : 'disconnected',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
