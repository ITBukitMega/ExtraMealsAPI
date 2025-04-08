<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestNEAT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    public function submitLeaveRequest(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'EmpName' => 'required|string',
            'SiteName' => 'required|string',
            'Shift' => 'required|string',
            'LeaveType' => 'required|string',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'Reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Status' => 'error',
                'Message' => 'Validation error',
                'Errors' => $validator->errors()
            ], 400);
        }

        try {
            // Insert into database using model
            LeaveRequestNEAT::create([
                'EmpID' => $request->EmpID,
                'EmpName' => $request->EmpName,
                'SiteName' => $request->SiteName,
                'Shift' => $request->Shift,
                'LeaveType' => $request->LeaveType,
                'StartDate' => $request->StartDate,
                'EndDate' => $request->EndDate,
                'Reason' => $request->Reason,
                'Status' => 'Pending',
                'CreatedAt' => now()
            ]);

            return response()->json([
                'Status' => 'success',
                'Message' => 'Leave request submitted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'error',
                'Message' => 'Failed to submit leave request',
                'Error' => $e->getMessage()
            ], 500);
        }
    }
}