<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use Illuminate\Support\Facades\Log;

class LoginControllerProduction extends Controller
{

    private $MINIMUM_APP_VERSION = "3.0.2";
    
    public function index()
    {
        $data = ListMasterLogin::all();

        return response()->json([
            "Status" => true,
            "message" => "Data ditemukan",
            "data" => $data
        ], 200);

        return view("test");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function login(Request $request)
    {

        Log::info('Incoming request', $request->all());
        // Validate request
        $validate = $request->validate([
            'EmpID' => 'required',
            'Password' => 'required',
            'appVersion' => 'required'
        ]);

        // Pastikan validasi berhasil sebelum melanjutkan
    if (!$validate) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed'
        ], 422);
    }


        Log::info('Validation passed'); // Log jika validasi berhasil

         // Check app version first
        if (!$this->isVersionCompatible($request->appVersion)) {
            return response()->json([
                'status' => false,
                'message' => 'Version not compatible',
                'requiredVersion' => $this->MINIMUM_APP_VERSION
            ], 426); // 426 Upgrade Required
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

        // Check if password matches the hashed one stored in the database
        if (!$user || !hash_equals($user->Password, md5($request->Password))) {
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
                // Tambahkan data user lain yang diperlukan di sini
            ]
        ], 200);
    }

    private function isVersionCompatible($clientVersion)
    {
        return version_compare($clientVersion, $this->MINIMUM_APP_VERSION, '>=');
    }
}
