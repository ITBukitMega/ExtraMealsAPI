<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TrAttendance; 
use App\Models\MasterSiteAllowed;
use App\Models\MasterSiteAllowedNEAT;
use App\Models\TrAttendanceNEAT;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    // Define allowed sites
    private $allowedSites = ['Jakarta', 'Lombok', 'TasikMalaya', 'Situbondo', 'Purwakarta'];

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

    private function findNearestSite($userLat, $userLong)
    {
        // Get all sites from the database
        $sites = MasterSiteAllowedNEAT::whereIn('SiteName', $this->allowedSites)->get();
        
        if ($sites->isEmpty()) {
            return [
                'status' => false,
                'message' => 'Tidak ada lokasi yang tersedia dalam database'
            ];
        }

        $nearestSite = null;
        $shortestDistance = PHP_FLOAT_MAX;
        $withinRadius = false;

        foreach ($sites as $site) {
            $distance = $this->calculateDistance(
                $userLat,
                $userLong,
                $site->latitude,
                $site->longitude
            );

            // Update the nearest site regardless of radius
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $nearestSite = $site;
                $withinRadius = ($distance <= $site->radius);
            }
            
            // If we find a site within radius, we can stop looking
            if ($withinRadius) {
                break;
            }
        }

        if (!$nearestSite) {
            return [
                'status' => false,
                'message' => 'Tidak dapat menemukan lokasi terdekat'
            ];
        }

        if (!$withinRadius) {
            return [
                'status' => false,
                'message' => 'Anda berada diluar radius yang diizinkan. Silahkan mendekat ke lokasi ' . $nearestSite->SiteName,
                'distance' => round($shortestDistance),
                'allowed_radius' => $nearestSite->radius,
                'site_location' => [
                    'latitude' => $nearestSite->latitude,
                    'longitude' => $nearestSite->longitude
                ],
                'site_name' => $nearestSite->SiteName
            ];
        }

        return [
            'status' => true,
            'site' => $nearestSite,
            'distance' => round($shortestDistance)
        ];
    }

    private function validateAppVersion($appVersion)
    {
        $minimumRequiredVersion = "1.0.0";
        
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
                'SiteName' => 'nullable', // Make SiteName optional, we'll determine it based on location
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
            
            // Find nearest site based on user's coordinates
            $siteResult = $this->findNearestSite(
                $request->Lattitude,
                $request->Longitude
            );

            if (!$siteResult['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $siteResult['message'],
                    'data' => [
                        'distance' => $siteResult['distance'] ?? null,
                        'allowed_radius' => $siteResult['allowed_radius'] ?? null,
                        'site_location' => $siteResult['site_location'] ?? null,
                        'site_name' => $siteResult['site_name'] ?? null
                    ]
                ], 400);
            }

            // Set timezone ke Jakarta
            $jakartaTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
            $jakartaDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            // Check if already checked in today
            $existingAttendance = TrAttendanceNEAT::where('EmpID', $request->EmpID)
                ->where('Date', $jakartaDate)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini'
                ], 400);
            }

            // Use the detected site name
            $actualSiteName = $siteResult['site']->SiteName;

            // Create new attendance record
            $attendance = TrAttendanceNEAT::create([
                'EmpID' => $request->EmpID,
                'Shift' => $request->Shift,
                'SiteName' => $actualSiteName, // Use the actual site name based on location
                'Date' => $jakartaDate,
                'CheckIn' => $jakartaTime,
                'Lattitude' => $request->Lattitude,
                'Longitude' => $request->Longitude,
                'AppVersion' => $request->AppVersion
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Check-in berhasil di ' . $actualSiteName,
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
            $attendance = TrAttendanceNEAT::where('EmpID', $request->EmpID)
                ->where('Date', $jakartaDate)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record attendance tidak ditemukan untuk hari ini'
                ], 404);
            }

            // Find nearest site based on user's current coordinates
            $siteResult = $this->findNearestSite(
                $request->Lattitude,
                $request->Longitude
            );

            if (!$siteResult['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $siteResult['message'],
                    'data' => [
                        'distance' => $siteResult['distance'] ?? null,
                        'allowed_radius' => $siteResult['allowed_radius'] ?? null,
                        'site_location' => $siteResult['site_location'] ?? null,
                        'site_name' => $siteResult['site_name'] ?? null
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
                'message' => 'Check-out berhasil di ' . $siteResult['site']->SiteName,
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

            $attendance = TrAttendanceNEAT::where('EmpID', $request->EmpID)
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