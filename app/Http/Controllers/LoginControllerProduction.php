<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginControllerProduction extends Controller
{
    private $MINIMUM_APP_VERSION = "3.0.2";

    public function login(Request $request)
    {
        Log::info('Incoming request', $request->all());

        // Gunakan Validator facade untuk validasi manual
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'Password' => 'required|string',
            'appVersion' => 'required|string'
        ]);

        // Jika validasi gagal, return response error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check app version first
        if (!$this->isVersionCompatible($request->appVersion)) {
            return response()->json([
                'status' => false,
                'message' => 'Version not compatible',
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

    private function isVersionCompatible($clientVersion)
    {
        return version_compare($clientVersion, $this->MINIMUM_APP_VERSION, '>=');
    }
}