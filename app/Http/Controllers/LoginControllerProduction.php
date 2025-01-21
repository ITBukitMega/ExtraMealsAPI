<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;

class LoginControllerProduction extends Controller
{
    
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
        // Validate request
        $request->validate([
            'EmpID' => 'required',
            'Password' => 'required'
        ]);

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
}
