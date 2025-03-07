<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListMasterLogin;
use Exception;

class LoginAttendanceProduction extends Controller
{
    public function index(){
        $data = ListMasterLogin::all();

        return response()->json([
            "status" => true,
            "data" => $data,
            "message" => "success"
        ]);
    }

    public function login(Request $request){
        try {
            $validate = $request->validate([
                "EmpID" => 'required|string',
                "Password" => 'required|string',
                "Module" => 'required|string',
            ]);

            $user = ListMasterLogin::where("EmpID", $request->EmpID)->first();


            if($user){
                $password = md5($validate["Password"]);
                $module = $validate["Module"];
                if($user->Password === $password && $module === 'Attendance'){
                    return response()->json([
                        "status" => true,
                        "data" => $user,
                        "message" => "Login berhasil!!!"
                    ], 200);
                }else {
                    return response()->json([
                        "status" => false,
                        "message" => "NIK atau Password Salah atau anda tidak punya akses untuk Module ini"
                    ], 401);
                }
            }else {
                return response()->json([
                    "status" => false,
                    "message" => "EmpID tidak ditemukan"
                ], 404);
            }
        }catch(Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "terjadi kesalahan " .$e->getMessage()
            ], 400);
        }
    }
}
