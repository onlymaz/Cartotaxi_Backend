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

    // ── Legacy paths the shipped iOS apps call (public) ──────────────────
    Route::post('social-login', 'Api\AuthController@socialLogin')->middleware('throttle:10,1');
    Route::post('business-account', 'Api\AuthController@registerBusiness')->middleware('throttle:5,1');
    Route::post('reset-password', 'Api\ResetPasswordController@create')->middleware('throttle:3,60');
    Route::post('reset-password/store', 'Api\ResetPasswordController@store')->middleware('throttle:5,60');
});

Route::group(['prefix' => 'v1', 'middleware' => ['authApi']], function () {
    Route::delete('user', 'Api\AuthController@DeleteUser');
    Route::put('user/fcm-token', 'Api\AuthController@update_fcm_token');
    Route::get('firebase-token', 'Api\AuthController@firebaseToken');
    Route::post('logout', 'Api\AuthController@logout');
    Route::get('dashboard/statistics', 'Api\StatisticsController@UserStats');
    Route::get('bookings', 'Api\BookingController@bookings');
    // The shipped iOS apps POST `bookings` (multipart) to fetch their booking
    // LIST and create orders via `booking-store/v2` — so POST bookings maps
    // to the list, matching the legacy contract the apps were built against.
    Route::post('bookings', 'Api\BookingController@bookings');
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

    // ── Legacy paths the shipped iOS apps call (authenticated) ──────────
    // Both apps send every request as multipart POST, so each alias accepts
    // POST and forwards to the same controller as its modern counterpart.
    Route::post('pending-orders', 'Api\AutoOrderController@index');                       // dispatch offers list
    Route::post('accept-order', 'Api\AutoOrderController@allowAutoRider');                // accept / reject the call
    Route::post('update-coordinates', 'Api\TrackingController@UpdateCoordinates');        // rider GPS heartbeat
    Route::post('update-booking-status', 'Api\BookingController@update_status');
    Route::post('update-payment-status', 'Api\BookingController@update_payment');
    Route::post('store-rating', 'Api\RatingController@store_rating');
    Route::post('update-profile', 'Api\SettingsController@profile_update');
    Route::post('deleteuser', 'Api\AuthController@DeleteUser');
    Route::match(['get', 'post'], 'user-statistics', 'Api\AuthController@statistics');
    Route::match(['get', 'post'], 'user-object', 'Api\AuthController@userObject');
    Route::post('show-helper', 'Api\HelperController@show');
    Route::post('add-helper', 'Api\HelperController@create');
    Route::post('ride-start-drop', 'Api\RiderRouteController@start_rider');
    Route::post('update-ride-drop-off-completed', 'Api\RiderRouteController@ride_completed');
    Route::post('send-file-to-rider', 'Api\AutoOrderController@sendFileToRider');

    // Stripe card payments (User app StripePaymentIntentService)
    Route::post('payments/stripe/payment-intent', 'Api\StripePaymentIntentController@create');
    Route::post('payments/stripe/payment-intent/confirm', 'Api\StripePaymentIntentController@confirm');
    Route::post('payments/stripe/payment-intent/cancel', 'Api\StripePaymentIntentController@cancel');
});
