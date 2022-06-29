<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
use Illuminate\Support\Facades\Validator;
use File;


class InsuranceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInsurances(Request $request)
    {
        $userId = $request->user_id;
        $insurances = Insurance::where('user_id', $userId)->get();
        if ($insurances->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Insurances"], 404);
        }

        return response()->json([
            "message" => "Success",
            "list" => $insurances
        ], 200);
    }


    public function postInsurance(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'name' => $request->name,
            'file_id' => $request->file_id,
            'user_id' => $userId,
        ];
        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'file_id' => 'required|mimes:pdf|max:5048',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 422);
        }

        $insurance = new Insurance();
        if ($request->file('file_id')) {
            $file = $request->file('file_id');
            $filename =  time() . $file->getClientOriginalName();
            $file->move(public_path('Insurances'), $filename);
            $insurance->file_id = 'Insurances/' . $filename;
        }

        $insurance->name = $request->name;
        $insurance->user_id = $userId;
        $insurance->save();
        return response()->json([
            "message" => "Success",
            "Insurance" => $insurance
        ], 201);
    }



    public function updateInsurance(Request $request, $insuranceId)
    {
        // $userId = $request->user_id;
        $insurance = Insurance::find($insuranceId);

        if (is_null($insurance)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|',
            // 'file_id' => 'mimes:doc,docx,pdf,txt|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 422);
        }

        if ($request->file('file_id')) {
            if (isset($insurance['file_id'])) {
                $path = public_path() . "/" . $insurance->file_id;
                unlink($path);
            }
            $file = $request->file('file_id');
            $filename =  time() . $file->getClientOriginalName();
            $file->move(public_path('Insurances'), $filename);
            $insurance->file_id = 'Insurances/' . $filename;
        }
        $insurance->name = is_null($request->name) ? $insurance->name : $request->name;

        $insurance->save();
        return response()->json(
            [
                "message" => "Updated Successfully",
                "insurance" => $insurance,
            ],
            201
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteInsurance(Request $request, $insuranceId)
    {
        // $userId = $request->user_id;

        $insurance = Insurance::find($insuranceId);

        if (is_null($insurance)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        if (isset($insurance['file_id'])) {
            $path = public_path() . "/" . $insurance->file_id;
            unlink($path);
        }
        $insurance->delete();
        return response()->json([
            "message" => "Deleted successfully"
        ], 204);
    }
}
