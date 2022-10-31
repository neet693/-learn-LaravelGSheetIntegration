<?php

use Illuminate\Support\Facades\Route;
use App\Services\GoogleSheet;

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

Route::get('/', function (GoogleSheet $googleSheet) {
    // $values = [
    //     [5, 'Gambar Presiden', 2],
    //     [6, 'Gambar Wakil Presiden', 2]
    // ];
    // $savedData = $googleSheet->writeSheet($values);
    // dump($savedData);
    // $googleSheet->readSheet($values);
    return view('welcome', compact('googleSheet'));
});
