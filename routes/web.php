<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/forget', function () {
    if(!Auth::guest()){
        return redirect()->route('login');
    }
    return view('auth/passwords/email');
});

Route::get('/verify/code','Auth\LoginController@verifyCode' )->name('verify.code');
Route::post('/verify/code','Auth\LoginController@verifyCode' )->name('verify.code');
Route::post('/logout','Auth\LoginController@logout' )->name('logout');

Route::get('login/{provider}', 'Auth\SocialLoginController@redirectToProvider');
Route::get('login/{provider}/callback', 'Auth\SocialLoginController@handleProviderCallback');

Auth::routes();
/*Localization*/
Route::get('/locale/{locale}', function ($locale){
    session(['locale'=> $locale]);
    return redirect()->back();
});
/*End Localization*/
Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth','roles'],'roles'=>['admin']],function() {
    Route::get('admin/dashboard','Admin\DashboardController@index')->name('admin.dashboard');
    // Diagnostic-only; not exposed in production environments.
    if (app()->environment('local', 'testing')) {
        Route::get('admin/dashboard/test-route','Admin\DashboardController@test_route');
    }
    Route::resource('users','Admin\UserController');
    Route::resource('districts','Admin\DistrictController');
    Route::resource('reviews','Admin\ReviewController');
    Route::resource('settings','Admin\SettingController');
    Route::resource('bookings','Admin\OrderRequestController');
    Route::get('dispatch-logs','Admin\DispatchLogController@index')->name('dispatch.logs');
    Route::resource('dispatcher','Admin\DispatcherController');
    Route::get('dispatcher/export/csv/{type}','Admin\DispatcherController@exportCsv')->name('dispatcher.export.csv');
    Route::get('dispatcher/export/pdf/{type}','Admin\DispatcherController@exportPdf')->name('dispatcher.export.pdf');
    Route::get('admin/reports','Admin\ReportController@index')->name('admin.reports');
    Route::resource('packages','Admin\PackageController');
    Route::post('rider/info','Admin\DispatcherController@riderDetail')->name('get_rider_info');
    Route::get('live/map','Admin\OrderRequestController@liveMap')->name('map.index');
    Route::get('live/map/ajax','Admin\OrderRequestController@liveMapAjax')->name('map.ajax');
    Route::get('live/tracking','Admin\OrderRequestController@liveMap')->name('map.live-tracking');


    // POST not GET: this clears bookings — destructive, must require CSRF.
    Route::post('bookings/clear','Admin\OrderRequestController@clear')->name('bookings.clear');

    Route::get('users/rider/popup/{id}','Admin\UserController@RiderPopup')->name('users.rider_popup');
    Route::patch('orders/rider/update/{id}','Admin\UserController@RiderOrderSave')->name('users.rider_order_save');
    Route::get('user-reviews','Admin\ReviewController@index')->name('reviews.index');
    Route::get('rider-reviews','Admin\ReviewController@riders')->name('reviews.riders');
    Route::post('district-change-status','Admin\DistrictController@change_status')->name('districts.change_status');
    Route::get('delete-modal/{id}','Admin\UserController@delete_modal')->name('users.delete_modal');
    Route::post('user-change-status','Admin\UserController@change_status')->name('users.change_status');
    Route::post('admin/dateFilter','Admin\DashboardController@dateFilter')->name('users.changeDate.filter');
});
Route::group(['middleware' => ['auth','roles'],'roles'=>['customer']],function() {
    Route::get('customer',function() {
       return redirect()->route('customer.dashboard');
    });
    Route::get('customer/dashboard','User\DashboardController@dashboard')->name('customer.dashboard');
    Route::get('customer/bookings','User\DashboardController@index')->name('customer.bookings');
    Route::get('customer/my-bookings','User\DashboardController@MyBookings')->name('customer.mybookings');
    
    Route::post('customer.bookings.RiderDetail','User\DashboardController@RiderDetail')->name('customer.bookings.RiderDetail');
    
    Route::post('customer.bookings.RateRider','User\DashboardController@RatingRider')->name('customer.bookings.RateRider');
    Route::post('customer/my-bookings','User\DashboardController@HelperInfo')->name('customer.bookings.helper-info-ajax');
    Route::post('customer/package-ajax-price' ,'User\DashboardController@ajaxGetPackagePrice');
});
Route::group(['middleware' => ['auth','roles'],'roles'=>['customer', 'admin', 'rider']],function() {
    Route::get('notificaitons','Admin\NotificationController@index')->name('notificaitons.show');
    // DELETE not GET: account deletion is destructive — must require CSRF.
    Route::delete('user/delete/{id}','Admin\SettingController@deleteUsere')->name('delete.user');
    Route::get('user/download/{id}','Admin\SettingController@downloadProfile')->name('dowbload.profile');
    Route::get('downloads/pdf/{id}','Admin\SettingController@createPDF')->name('createPDF.profile');
    Route::post('dispatcher/store','Admin\DispatcherController@store')->name('dispatcher.store');
    Route::get('my-profile/{id}','Admin\SettingController@profile_view')->name('settings.profile_view');
    Route::get('change-password/{id}','Admin\SettingController@change_password')->name('settings.change_password');
    Route::patch('my-profile/{id}/update','Admin\SettingController@profile_update')->name('settings.profile_update');
    Route::get('dispatcher/index/getCustomers','Admin\DispatcherController@getCustomers')->name('getCustomers');
    Route::post('dispatcher/index/select-payment','Admin\DispatcherController@SelectPaymentGateway')->name('SelectPaymentGateway');
    Route::get('geocode','Admin\DispatcherController@geocode')->name('geocode');
    Route::post('booking/CreateTransaction/payment','Admin\DispatcherController@CreatePaymentTransaction')->name('CreatePaymentTransaction');
    Route::post('save-device-token','Admin\DispatcherController@getfcm_token')->name('notificaiton');
});

Route::group(['middleware' => ['auth','roles'],'roles'=>['rider']],function() {
    Route::get('driver/dashboard','Rider\DashboardController@dashboard')->name('rider.dashboard');
    Route::get('driver/my-bookings','Rider\DashboardController@bookings')->name('rider.bookings');
});
Route::group(['middleware' => ['auth','roles'],'roles'=>[ 'admin', 'rider']],function() {
    Route::get('orders/change-status/{id}','Admin\OrderRequestController@change_status_popup')->name('bookings.change_status_popup');
    Route::patch('orders/change-status/{id}/save','Admin\OrderRequestController@change_status')->name('bookings.change_status');
    Route::get('bookings/show/{id}','Admin\OrderRequestController@show')->name('bookings.show');

});
Route::group(['middleware' => ['auth','roles'],'roles'=>[ 'admin', 'customer']],function() {
    Route::resource('helper','Helper\HelperController');
    Route::post('helper/store','Helper\HelperController@store');
    Route::post('helper/store/admin','Helper\HelperController@storeAdmin');
    Route::post('helper/change_status/{id}','Helper\HelperController@change_status')->name('helper.change_status');
    Route::post('helper/get_time','Helper\HelperController@getTime')->name('getTime');
    Route::get('helper/fee','Helper\HelperController@show')->name('helper.show-fee');
    Route::post('helper/fee/store','Helper\HelperController@storeFee');
    Route::post('customer/order/change-status','Admin\OrderRequestController@OrderStatusCustomer')->name('customer.bookings.change_status_popup');

});
