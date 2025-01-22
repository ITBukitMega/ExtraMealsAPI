<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TrAttendance;
use Illuminate\Http\Request;

class AttendanceProductionController extends Controller
{
    public function checkIn(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'EmpID' => 'required',
                'Shift' => 'required',
                'SiteName' => 'required',
                'Lattitude' => 'required',
                'Longitude' => 'required'
            ]);

            // Set timezone ke Jakarta
            $jakartaTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
            $jakartaDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            // Check if already checked in today
            $existingAttendance = TrAttendance::where('EmpID', $request->EmpID)
                ->where('Date', $jakartaDate)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini'
                ], 400);
            }

            // Create new attendance record
            $attendance = TrAttendance::create([
                'EmpID' => $request->EmpID,
                'Shift' => $request->Shift,
                'SiteName' => $request->SiteName,
                'Date' => $jakartaDate,
                'CheckIn' => $jakartaTime,
                'Lattitude' => $request->Lattitude,
                'Longitude' => $request->Longitude
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Check-in berhasil',
                'data' => $attendance
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkOut(Request $request)
    {
        try {
            // Validate request - only EmpID required now
            $request->validate([
                'EmpID' => 'required'
            ]);

            // Set timezone ke Jakarta
            $jakartaTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
            $jakartaDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            // Find today's attendance record
            $attendance = TrAttendance::where('EmpID', $request->EmpID)
                ->where('Date', $jakartaDate)
                ->first();

                if (!$attendance) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Record attendance tidak ditemukan untuk hari ini'
                    ], 404);
                }

            // Update checkout time only
            $attendance->update([
                'CheckOut' => $jakartaTime
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Check-out berhasil',
                'data' => $attendance
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus(Request $request)
{
    try {
        $request->validate([
            'EmpID' => 'required',
            'Date' => 'required|date_format:Y-m-d'
        ]);

        $attendance = TrAttendance::where('EmpID', $request->EmpID)
            ->where('Date', $request->Date)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Status retrieved successfully',
            'data' => $attendance
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}
