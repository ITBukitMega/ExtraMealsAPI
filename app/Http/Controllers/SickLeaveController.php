<?php

namespace App\Http\Controllers;

use App\Models\SickLeaveRequestNEAT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SickLeaveController extends Controller
{
    public function submitSickLeave(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'EmpID' => 'required|string',
            'EmpName' => 'required|string',
            'SiteName' => 'required|string',
            'Shift' => 'required|string',
            'SicknessType' => 'required|string',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'StartTime' => 'required|string',
            'EndTime' => 'required|string',
            'Condition' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'Status' => false,
                'Message' => 'Validation error',
                'Errors' => $validator->errors()
            ], 400);
        }

        try {
            // Insert into database using model
            SickLeaveRequestNEAT::create([
                'EmpID' => $request->EmpID,
                'EmpName' => $request->EmpName,
                'SiteName' => $request->SiteName,
                'Shift' => $request->Shift,
                'SicknessType' => $request->SicknessType,
                'StartDate' => $request->StartDate,
                'EndDate' => $request->EndDate,
                'StartTime' => $request->StartTime,
                'EndTime' => $request->EndTime,
                'Condition' => $request->Condition,
                'Status' => 'Pending',
                'CreatedAt' => now()
            ]);

            return response()->json([
                'Status' => true,
                'Message' => 'Sick leave request submitted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => false,
                'Message' => 'Failed to submit sick leave request',
                'Error' => $e->getMessage()
            ], 500);
        }
    }
}
