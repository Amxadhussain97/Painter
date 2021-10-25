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
    public function getInsurances()
    {
        $userId = auth()->user()->id;
        $insurances = Insurance::select('name')->where('user_id',$userId)->get();
        if($insurances->isEmpty()){
            return response()->json(["message" => "This User Doesn't have any Insurances"],404);
        }

        return response()->json( [
            "message" => "Success",
            "insurance" => $insurances
        ] ,200);
    }


    public function postInsurance(Request $request)
    {
        $userId = auth()->user()->id;

        $r = [
            'name' => $request->name,
            'file_id' => $request->file_id,
            'user_id' => $userId,
        ];
        $validator = Validator::make($r,
            [
                'name' => 'required|max:255|min:3',
                'file_id' => 'required|mimes:doc,docx,pdf,txt|max:2048',
            ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $insurance = new Insurance();
        if($request->file('file_id'))
        {
            $file = $request->file('file_id');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Insurances'),$filename);
            $insurance->file_id = $filename;

        }

        $insurance->name = $request->name;
        $insurance->user_id = $userId;
        $insurance->save();
        return response()->json([
            "message" => "Success",
            "Insurance" => $insurance->name],201);


    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function updateInsurance(Request $request,$insuranceId)
    {
        $userId = auth()->user()->id;
        $insurance = Insurance::find($insuranceId);

        if(is_null($insurance) || $insurance->user_id != $userId){
            return response()->json(["message" => "Record Not Found!"],404);
        }
        $rules = [
            'name' => 'max:255|min:3',
            'file_id' => 'mimes:doc,docx,pdf,txt|max:2048',
        ];

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        if ($request->file('file_id')){
            if(isset($insurance['file_id']))
            {
                $path = public_path()."/Insurances/".$insurance->file_id;
                unlink($path);
            }
            $file = $request->file('file_id');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Insurances'),$filename);
            $insurance->file_id = $filename;
        }
        $insurance->name = is_null($request->name) ? $insurance->name : $request->name;

        $insurance->save();
        return response()->json(
            [
                "message" => "Updated Successfully",
                "insurance" => $insurance
            ],200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteInsurance($insuranceId)
    {
        $userId = auth()->user()->id;

        $insurance = Insurance::find($insuranceId);

        if(is_null($insurance) || $insurance->user_id != $userId){
            return response()->json(["message" => "Record Not Found!"],404);
        }

        if(isset($insurance['file_id']))
        {
            $path = public_path()."/Insurances/".$insurance->file_id;
            unlink($path);
        }
        $insurance->delete();
        return response()->json([
            "message" => "Deleted successfully"
        ],201);
    }
}
