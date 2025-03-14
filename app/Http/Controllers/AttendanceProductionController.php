<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TrAttendance;
use App\Models\MasterSiteAllowed;
use Illuminate\Http\Request;

class AttendanceProductionController extends Controller
{
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        // Earth's radius in meters
        $r = 6371000;
        
        return $r * $c; // Returns distance in meters
    }

    private function validateLocation($siteName, $userLat, $userLong)
    {
        $site = MasterSiteAllowed::where('SiteName', $siteName)->first();
        
        if (!$site) {
            return [
                'status' => false,
                'message' => 'Lokasi site tidak ditemukan dalam database'
            ];
        }

        $distance = $this->calculateDistance(
            $userLat,
            $userLong,
            $site->latitude,
            $site->longitude
        );

        if ($distance > $site->radius) {
            return [
                'status' => false,
                'message' => 'Anda berada diluar radius yang diizinkan. Silahkan mendekat ke lokasi site.',
                'distance' => round($distance),
                'allowed_radius' => $site->radius,
                'site_location' => [
                    'latitude' => $site->latitude,
                    'longitude' => $site->longitude
                ]
            ];
        }

        return ['status' => true];
    }

    private function validateAppVersion($appVersion)
    {
        $minimumRequiredVersion = "3.1.0";
        
        if (empty($appVersion)) {
            return [
                'status' => false,
                'message' => 'App version is required'
            ];
        }
        
        // Simple version comparison (you might want to use a more sophisticated version comparison)
        if (version_compare($appVersion, $minimumRequiredVersion, '<')) {
            return [
                'status' => false,
                'message' => 'Versi aplikasi Anda sudah tidak didukung. Silahkan update ke versi terbaru.'
            ];
        }
        
        return ['status' => true];
    }

    public function checkIn(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'EmpID' => 'required',
                'Shift' => 'required',
                'SiteName' => 'required',
                'Lattitude' => 'required',
                'Longitude' => 'required',
                'AppVersion' => 'required'
            ]);

            // Validate app version
            $appVersionValidation = $this->validateAppVersion($request->AppVersion);
            if (!$appVersionValidation['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $appVersionValidation['message']
                ], 400);
            }
            
            // Validate location first
            $locationValidation = $this->validateLocation(
                $request->SiteName,
                $request->Lattitude,
                $request->Longitude
            );

            if (!$locationValidation['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $locationValidation['message'],
                    'data' => [
                        'distance' => $locationValidation['distance'] ?? null,
                        'allowed_radius' => $locationValidation['allowed_radius'] ?? null,
                        'site_location' => $locationValidation['site_location'] ?? null
                    ]
                ], 400);
            }

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
                'Longitude' => $request->Longitude,
                'AppVersion' => $request->AppVersion
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
            // Validate request
            $request->validate([
                'EmpID' => 'required',
                'Lattitude' => 'required',
                'Longitude' => 'required',
                'AppVersion' => 'required'
            ]);
            
            // Validate app version
            $appVersionValidation = $this->validateAppVersion($request->AppVersion);
            if (!$appVersionValidation['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $appVersionValidation['message']
                ], 400);
            }

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

            // Validate location
            $locationValidation = $this->validateLocation(
                $attendance->SiteName,
                $request->Lattitude,
                $request->Longitude
            );

            if (!$locationValidation['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $locationValidation['message'],
                    'data' => [
                        'distance' => $locationValidation['distance'] ?? null,
                        'allowed_radius' => $locationValidation['allowed_radius'] ?? null,
                        'site_location' => $locationValidation['site_location'] ?? null
                    ]
                ], 400);
            }

            // Update checkout time and app version
            $attendance->update([
                'CheckOut' => $jakartaTime,
                'AppVersionOut' => $request->AppVersion
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