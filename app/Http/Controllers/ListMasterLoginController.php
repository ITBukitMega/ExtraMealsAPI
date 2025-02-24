<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use App\Http\Requests\StoreListMasterLoginRequest;
use App\Http\Requests\UpdateListMasterLoginRequest;

class ListMasterLoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
    // Define current app version
    $CURRENT_VERSION = "3.1.0"; // Sesuaikan dengan versi terbaru aplikasi

    // Validate request
    $request->validate([
        'EmpID' => 'required',
        'Password' => 'required',
        'Version' => 'required'
    ]);

    // Check app version
    if ($request->Version !== $CURRENT_VERSION) {
        return response()->json([
            'status' => false,
            'message' => 'Versi aplikasi tidak sesuai',
            'current_version' => $CURRENT_VERSION,
            'updateRequired' => true,
            'updateMessage' => 'Silakan update aplikasi ke versi terbaru untuk melanjutkan',
            'storeUrl' => 'https://your-app-store-url.com' // Sesuaikan dengan URL store aplikasi Anda
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



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListMasterLoginRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ListMasterLogin $listMasterLogin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ListMasterLogin $listMasterLogin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateListMasterLoginRequest $request, ListMasterLogin $listMasterLogin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ListMasterLogin $listMasterLogin)
    {
        //
    }
}
