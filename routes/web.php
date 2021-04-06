<?php

use Illuminate\Support\Facades\Route;
use App\Bundles\Documents\DocumentsDownload;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/download/uploads/files/{model}/{model_id}/{name}', [DocumentsDownload::class, 'downloadDocument']);
Route::get('/download/uploads/files/{model}/{model_id}', [DocumentsDownload::class, 'downloadDocuments']);

Route::get('/', function () {
    return view('app');
});

//
//Route::get('/test', function () {
//    $users = App\Models\User::get();
//    $users->load(['roles']);
//    dd($users);
//});

Route::view('/{any}', 'app')->where('any', '.*');
