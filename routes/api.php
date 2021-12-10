<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CertificateController;
use \App\Http\Controllers\InsuranceController;
use \App\Http\Controllers\GalleryController;
use \App\Http\Controllers\PhotosController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\EptoolController;
use \App\Http\Controllers\AdminController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::get('/test/otp-request', [UserController::class,'requestForOtp']);

Route::post("register", [UserController::class, 'register']);
Route::post("login", [UserController::class, 'login'])->name('login');

Route::group(["middleware" => ["auth:api"]], function () {

    Route::get("profile", [UserController::class, 'profile']);
    Route::get("logout", [UserController::class, 'logout']);
    Route::post("profile", [UserController::class, 'updateProfile']);


    Route::group([
        'middleware' => ['userMiddleware','cors']


], function () {
        Route::get("users", [AdminController::class, 'getUsers']); // TO GET ALL USERS
        Route::post('users/{User::id}', [AdminController::class, 'updateUser']); // TO UPDATE AN USER
        Route::get('users/{User::id}', [AdminController::class, 'getUser']); // TO UPDATE AN USER

        Route::post('eptools', [EptoolController::class, 'postEptool']); // TO POST AN EPTOOL
        Route::get('eptools', [EptoolController::class, 'getEptools']); // TO GET EPTOOL OF AN USER
        Route::post('eptools/{Eptool::id}', [EptoolController::class, 'updateEptool']); // TO UPDATE AN USER'S EPTOOL
        Route::delete('eptools/{Eptool::id}', [EptoolController::class, 'deleteEptool']); // TO DELETE AN USER'S EPTOOL

        Route::post('eptools/{Eptool::id}/photos', [EptoolController::class, 'postEpphoto']); // TO POST AN EPTOOL
        Route::get('eptools/{Eptool::id}/photos', [EptoolController::class, 'getEpphotos']); // TO GET EPTOOL OF AN USER
        Route::post('eptools/{Eptool::id}/photos/{Epphoto::id}', [EptoolController::class, 'updateEpphoto']); // TO UPDATE AN USER'S EPTOOL
        Route::delete('eptools/{Eptool::id}/photos/{Epphoto::id}', [EptoolController::class, 'deleteEpphoto']); // TO DELETE AN USER'S EPTOOL

        Route::post('certificates', [CertificateController::class, 'postCertificate']); // TO POST A CERTIFICATE
        Route::get('certificates', [CertificateController::class, 'getCertificates']); // TO GET CERTIFICATES OF AN USER
        Route::post('certificates/{Certificate::id}', [CertificateController::class, 'updateCertificate']); // TO UPDATE AN USER'S CERTIFICATE
        Route::delete('certificates/{Certificate::id}', [CertificateController::class, 'deleteCertificate']); // TO DELETE AN USER'S CERTIFICATE

        Route::post('insurances', [InsuranceController::class, 'postInsurance']); // TO POST AN INSURANCE
        Route::get('insurances', [InsuranceController::class, 'getInsurances']); // TO GET INSURANCES OF AN USER
        Route::post('insurances/{Insurance::id}', [InsuranceController::class, 'updateInsurance']); // TO UPDATE AN USER'S INSURANCE
        Route::delete('insurances/{Insurance::id}', [InsuranceController::class, 'deleteInsurance']);  // TO DELETE AN USER'S INSURANCE

        Route::post('galleries', [GalleryController::class, 'postGallery']); // TO POST A GALLERY
        Route::get('galleries', [GalleryController::class, 'getGalleries']); // TO GET GALLERIES OF AN USER
        Route::post('galleries/{Gallery::id}', [GalleryController::class, 'updateGallery']); // TO UPDATE AN USER'S GALLERY
        Route::delete('galleries/{Gallery::id}', [GalleryController::class, 'deleteGallery']); // TO DELETE AN USER'S GALLERY

        Route::post('galleries/{Gallery::id}/photos', [GalleryController::class, 'postUserPhoto']); // TO POST A PHOTO
        Route::get('galleries/{Gallery::id}/photos', [GalleryController::class, 'getUserPhotos']); // TO GET PHOTOS OF A GALLERY
        Route::post('galleries/{Gallery::id}/photos/{Photo::id}', [GalleryController::class, 'updateUserPhoto']); // TO UPDATE A GALLERY PHOTO
        Route::delete('galleries/{Gallery::id}/photos/{Photo::id}', [GalleryController::class, 'deleteUserPhoto']); // TO DELETE A GALLERY PHOTO

        Route::post('subpainters', [UserController::class, 'postSubpainter']); // TO POST A GALLERY
        Route::get('subpainters', [UserController::class, 'getSubpainters']); // TO GET GALLERIES OF AN USER
        Route::post('subpainters/{Subpainter::id}', [UserController::class, 'updateSubpainter']); // TO UPDATE AN USER'S GALLERY
        Route::delete('subpainters/{Subpainter::id}', [UserController::class, 'deleteSubpainter']); // TO DELETE AN USER'S GALLERY

        Route::post('dealers', [UserController::class, 'postDealer']); // TO POST A GALLERY
        Route::get('dealers', [UserController::class, 'getDealers']); // TO GET GALLERIES OF AN USER
        Route::post('dealers/{Dealer::id}', [UserController::class, 'updateDealer']); // TO UPDATE AN USER'S GALLERY
        Route::delete('dealers/{Dealer::id}', [UserController::class, 'deleteDealer']); // TO DELETE AN USER'S GALLERY

        Route::post('leads', [UserController::class, 'postLead']); // TO POST A GALLERY
        Route::get('leads', [UserController::class, 'getLeads']); // TO GET GALLERIES OF AN USER
        Route::post('leads/{Lead::id}', [UserController::class, 'updateLead']); // TO UPDATE AN USER'S GALLERY
        Route::delete('leads/{Lead::id}', [UserController::class, 'deleteLead']); // TO DELETE AN USER'S GALLERY



    });
});
