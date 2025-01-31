<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginControllerProduction extends Controller
{
    private $MINIMUM_APP_VERSION = "3.1.0";

    public function login(Request $request)
    {
        Log::info('Incoming request', $request->all());

        // Validate request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'Password' => 'required|string',
            'appVersion' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check app version compatibility
        if (version_compare($request->appVersion, $this->MINIMUM_APP_VERSION, '<')) {
            return response()->json([
                'status' => false,
                'message' => 'Please update your app to continue',
                'requiredVersion' => $this->MINIMUM_APP_VERSION
            ], 426);
        }

        // Find user by EmpID
        $user = ListMasterLogin::where('EmpID', $request->EmpID)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'EmpID tidak ditemukan'
            ], 404);
        }

        // Check if password matches
        if (!hash_equals($user->Password, md5($request->Password))) {
            return response()->json([
                'status' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // Login successful
        return response()->json([
            'status' => true,
            'message' => 'Login Successful, Welcome Back',
            'data' => [
                'EmpID' => $user->EmpID,
                'SiteName' => $user->SiteName,
                'Shift' => $user->Shift,
            ]
        ], 200);
    }
}