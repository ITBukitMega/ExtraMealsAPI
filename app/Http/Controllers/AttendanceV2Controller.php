<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TrAttendanceV2;
use App\Models\MasterSiteAllowedNEAT;
use Illuminate\Http\Request;

class AttendanceV2Controller extends Controller
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
        
        if (version_compare($appVersion, $minimumRequiredVersion, '<')) {
            return [
                'status' => false,
                'message' => 'Versi aplikasi Anda sudah tidak didukung. Silahkan update ke versi terbaru.'
            ];
        }
        
        return ['status' => true];
    }
    
    public function recordAttendance(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'EmpID' => 'required',
                'Shift' => 'required',
                'SiteName' => 'nullable',
                'Type' => 'required|in:Check In,Check Out',
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
            
            // Use the detected site name
            $actualSiteName = $siteResult['site']->SiteName;
            
            // Create new attendance record - always create a new record
            $attendance = new TrAttendanceV2();
            $attendance->EmpID = $request->EmpID;
            $attendance->SiteName = $actualSiteName;
            $attendance->Date = $jakartaDate;
            $attendance->Time = $jakartaTime;
            $attendance->Type = $request->Type;
            $attendance->Lattitude = $request->Lattitude;
            $attendance->Longitude = $request->Longitude;
            $attendance->Shift = $request->Shift;
            $attendance->save();
            
            // Get recent attendance records for this user
            $recentRecords = TrAttendanceV2::where('EmpID', $request->EmpID)
                ->orderBy('Date', 'desc')
                ->orderBy('Time', 'desc')
                ->take(10)
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => $request->Type . ' berhasil di ' . $actualSiteName,
                'data' => [
                    'current' => $attendance,
                    'recent' => $recentRecords
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getAttendanceLog(Request $request)
    {
        try {
            $request->validate([
                'EmpID' => 'required',
                'Date' => 'nullable|date_format:Y-m-d'
            ]);
            
            $query = TrAttendanceV2::where('EmpID', $request->EmpID);
            
            // Filter by date if provided
            if ($request->has('Date') && !empty($request->Date)) {
                $query->where('Date', $request->Date);
            } else {
                // Default to the current month
                $firstDayOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
                $lastDayOfMonth = Carbon::now('Asia/Jakarta')->endOfMonth()->format('Y-m-d');
                $query->whereBetween('Date', [$firstDayOfMonth, $lastDayOfMonth]);
            }
            
            $records = $query->orderBy('Date', 'desc')
                            ->orderBy('Time', 'desc')
                            ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $records
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}