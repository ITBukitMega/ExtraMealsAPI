<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use App\Models\MasterLogin;
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
                'EmpName' => $user->EmpName,
                'SiteName' => $user->SiteName,
                'Shift' => $user->Shift,
                'Used' => $user->Used, // Added Used field to response
            ]
        ], 200);
    }
    
    public function checkVersion(Request $request)
    {
        Log::info('Version check request', $request->all());
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
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
                'requiredVersion' => $this->MINIMUM_APP_VERSION,
                'updateRequired' => true
            ], 426);
        }

        // Find user by EmpID (optional, to verify user exists)
        $user = ListMasterLogin::where('EmpID', $request->EmpID)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'EmpID tidak ditemukan'
            ], 404);
        }

        // Version check successful
        return response()->json([
            'status' => true,
            'message' => 'Versi aplikasi valid',
            'data' => [
                'EmpID' => $user->EmpID,
                'EmpName' => $user->EmpName,
                'SiteName' => $user->SiteName,
                'Shift' => $user->Shift,
                'Used' => $user->Used
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|regex:/^(?=.*[A-Za-z])(?=.*\d).*$/',
        ], [
            'newPassword.regex' => 'Password baru harus mengandung minimal 1 huruf dan 1 angka',
            'newPassword.min' => 'Password baru minimal harus 8 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user from ListMasterLogin to verify old password
        $user = ListMasterLogin::where('EmpID', $request->EmpID)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'EmpID tidak ditemukan'
            ], 404);
        }

        // Verify old password
        if (!hash_equals($user->Password, md5($request->oldPassword))) {
            return response()->json([
                'status' => false,
                'message' => 'Password lama salah'
            ], 401);
        }

        // Check if new password is the default password
        if ($request->newPassword === 'hallo123') {
            return response()->json([
                'status' => false,
                'message' => 'Password baru tidak boleh sama dengan password default'
            ], 400);
        }

        // Update password in MasterLogin (not ListMasterLogin since it's a view)
        $masterLoginUser = MasterLogin::where('EmpID', $request->EmpID)->first();

        if (!$masterLoginUser) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan di MasterLogin'
            ], 404);
        }

        // Update password and set Used to 1
        $masterLoginUser->Password = md5($request->newPassword);
        $masterLoginUser->Used = 1;
        $masterLoginUser->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah',
            'data' => [
                'EmpID' => $user->EmpID,
                'SiteName' => $user->SiteName,
                'Shift' => $user->Shift,
                'Used' => 1, // Now it's set to 1
            ]
        ], 200);
    }
}