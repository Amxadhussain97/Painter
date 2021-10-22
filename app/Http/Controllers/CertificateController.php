<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Validator;
use File;


class CertificateController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCertificate()
    {
        $userId = auth()->user()->id;


        $certificates = Certificate::select('name')->where('user_id',$userId)->get();
        if($certificates->isEmpty()){
            return response()->json(["message" => "This User Doesn't have any Insurences"],404);
        }

        return response()->json(
            [ "message" => 'success',
            "certificates" => $certificates ], 200);
    }






    public function postCertificate(Request $request)
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
                'user_id' => 'required|exists:users,id',
            ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $certificate = new Certificate();
        if($request->file('file_id'))
        {
            $file = $request->file('file_id');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Certificates'),$filename);
            $certificate->file_id = $filename;
        }

        $certificate->name = $request->name;
        $certificate->user_id = $userId;
        $certificate->save();
        return response()->json(
            [
                "message" => "Success",
                "certificate" => $certificate->name],201);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCertificate(Request $request, $certificateId)
    {
        $userId = auth()->user()->id;
        $certificate = Certificate::find($certificateId);
        if(is_null($certificate) || $certificate->user_id != $userId){
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
            if(isset($certificate['file_id']))
            {
                $path = public_path()."/Certificates/".$certificate->file_id;
                unlink($path);
            }
            $file = $request->file('file_id');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Certificates'),$filename);
            $certificate->file_id = $filename;
        }
        $certificate->name = is_null($request->name) ? $certificate->name : $request->name;
        $certificate->save();
        return response()->json([
            "message" => 'Updated Successfully',
        ],200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCertificate($certificateId)
    {
        $userId = auth()->user()->id;
        $certificate = Certificate::find($certificateId);

        if(is_null($certificate) || $certificate->user_id != $userId){
            return response()->json(["message" => "Record Not Found!"],404);
        }
        if(isset($certificate['file_id']))
        {
            $path = public_path()."/Certificates/".$certificate->file_id;
            unlink($path);
        }
        $certificate->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ],201);
    }
}
