<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequestNEAT;
use App\Models\SickLeaveRequestNEAT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InboxController extends Controller
{
    public function getInbox(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'Month' => 'sometimes|integer|min:1|max:12',
            'Year' => 'sometimes|integer|min:2000|max:2100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $empId = $request->EmpID;
            
            // Get month and year from request, or use current month/year
            $month = $request->has('Month') ? $request->Month : Carbon::now()->month;
            $year = $request->has('Year') ? $request->Year : Carbon::now()->year;
            
            // Build date range for filtering
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

            // Get leave requests for the employee within date range
            $leaveRequests = LeaveRequestNEAT::where('EmpID', $empId)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('StartDate', [$startDate, $endDate])
                          ->orWhereBetween('EndDate', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('StartDate', '<', $startDate)
                                ->where('EndDate', '>', $endDate);
                          });
                })
                ->orderBy('CreatedAt', 'desc')
                ->get();

            // Get sick leave requests for the employee within date range
            $sickLeaveRequests = SickLeaveRequestNEAT::where('EmpID', $empId)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('StartDate', [$startDate, $endDate])
                          ->orWhereBetween('EndDate', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('StartDate', '<', $startDate)
                                ->where('EndDate', '>', $endDate);
                          });
                })
                ->orderBy('CreatedAt', 'desc')
                ->get();

            // Format the data for the response
            $formattedLeaveRequests = $this->formatLeaveRequests($leaveRequests);
            $formattedSickLeaveRequests = $this->formatSickLeaveRequests($sickLeaveRequests);

            return response()->json([
                'status' => true,
                'message' => 'Inbox data retrieved successfully',
                'data' => [
                    'leave_requests' => $formattedLeaveRequests,
                    'sick_leave_requests' => $formattedSickLeaveRequests
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve inbox data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format leave requests for response
     */
    private function formatLeaveRequests($leaveRequests)
    {
        $formatted = [];
        
        foreach ($leaveRequests as $request) {
            $formatted[] = [
                'ID' => $request->ID,
                'EmpID' => $request->EmpID,
                'LeaveType' => $request->LeaveType,
                'StartDate' => $request->StartDate,
                'EndDate' => $request->EndDate,
                'Reason' => $request->Reason,
                'Status' => $request->Status,
                'CreatedAt' => $request->CreatedAt
            ];
        }
        
        return $formatted;
    }

    /**
     * Format sick leave requests for response
     */
    private function formatSickLeaveRequests($sickLeaveRequests)
    {
        $formatted = [];
        
        foreach ($sickLeaveRequests as $request) {
            $formatted[] = [
                'ID' => $request->ID,
                'EmpID' => $request->EmpID,
                'SicknessType' => $request->SicknessType,
                'StartDate' => $request->StartDate,
                'EndDate' => $request->EndDate,
                'StartTime' => $request->StartTime,
                'EndTime' => $request->EndTime,
                'Condition' => $request->Condition,
                'Status' => $request->Status,
                'CreatedAt' => $request->CreatedAt
            ];
        }
        
        return $formatted;
    }
}