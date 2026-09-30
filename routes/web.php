<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dipakai sebagai Privacy Policy URL di App Store Connect / TestFlight.
Route::view('/privacy', 'privacy')->name('privacy');
