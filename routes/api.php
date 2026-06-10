<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'v1'], function () {
    // Auth endpoints — tight rate limits to deter brute force / enumeration
    Route::post('register', 'Api\AuthController@register')->middleware('throttle:5,1');
    Route::post('register/confirmation', 'Api\AuthController@codeConfirmed')->middleware('throttle:10,1');
    Route::post('login', 'Api\AuthController@login')->middleware('throttle:5,1');
    Route::post('re-send-confirmation-code', 'Api\AuthController@ReSendConfirmationCode')->middleware('throttle:3,1');
    Route::post('password/email', 'Api\ResetPasswordController@create')->middleware('throttle:3,60');
    Route::post('password/reset', 'Api\ResetPasswordController@store')->middleware('throttle:5,60');

    // Public metadata
    Route::match(['get', 'post'], 'settings/allowed-area', 'Api\SettingsController@AllowedArea');
    Route::match(['get', 'post'], 'settings/allowed-area/ios', 'Api\SettingsController@AllowedAreaIos');
    Route::match(['get', 'post'], 'languages', 'Api\TranslationController@languagesType');
    Route::match(['get', 'post'], 'translations', 'Api\TranslationController@language');
    Route::match(['get', 'post'], 'get-translations', 'Api\TranslationController@language');
    Route::get('helper-fee', 'Api\SettingsController@HelperDetail');
});

Route::group(['prefix' => 'v1', 'middleware' => ['authApi']], function () {
    Route::delete('user', 'Api\AuthController@DeleteUser');
    Route::put('user/fcm-token', 'Api\AuthController@update_fcm_token');
    Route::post('logout', 'Api\AuthController@logout');
    Route::get('dashboard/statistics', 'Api\StatisticsController@UserStats');
    Route::get('bookings', 'Api\BookingController@bookings');
    // `bookings/storeo` was a typo of `store` and pointed at a non-existent
    // controller method (would 500 on every call). Removed; mobile clients
    // should use POST /bookings.
    Route::post('bookings', 'Api\BookingController@store');
    Route::post('booking-store/v2', 'Api\BookingController@store');
    Route::put('bookings/{id}/status', 'Api\BookingController@update_status');
    Route::get('auto-orders', 'Api\AutoOrderController@index');
    Route::post('auto-orders/response', 'Api\AutoOrderController@allowAutoRider');
    Route::put('payments/{id}/status', 'Api\BookingController@update_payment');
    Route::get('payments', 'Api\PaymentController@payments');
    Route::post('ratings', 'Api\RatingController@store_rating');
    Route::put('profile', 'Api\SettingsController@profile_update');
    Route::put('coordinates', 'Api\TrackingController@UpdateCoordinates');
    Route::post('helpers', 'Api\HelperController@create');
    Route::get('helpers/{id}', 'Api\HelperController@show');
    Route::get('user/statistics', 'Api\AuthController@statistics');
});
