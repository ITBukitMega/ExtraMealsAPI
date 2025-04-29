<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequestNEAT;
use App\Models\SickLeaveRequestNEAT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InboxController extends Controller
{
    public function getInbox(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string'
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

            // Get leave requests for the employee
            $leaveRequests = LeaveRequestNEAT::where('EmpID', $empId)
                ->orderBy('CreatedAt', 'desc')
                ->get();

            // Get sick leave requests for the employee
            $sickLeaveRequests = SickLeaveRequestNEAT::where('EmpID', $empId)
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