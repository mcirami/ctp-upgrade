<?php

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

use App\Privilege;
use Illuminate\Support\Facades\Route;
use LeadMax\TrackYourStats\User\Permissions;

Route::get('/', 'IndexController@index');
Route::post('/', 'IndexController@index');
Route::any('/resources/landers/{subDomain}/{asset}', 'LanderController@getAsset')->where('asset', '.*');
Route::get('/logout', 'LegacyLoginController@logout');
Route::post('email/incoming', 'RelevanceReactorController@incomingEmail');
Route::post('email/incoming/distribute', 'RelevanceReactorController@distributeEmail');
Route::group(['middleware' => 'legacy.auth'], function () {
    Route::get('dashboard', 'DashboardController@home');
    Route::group(['prefix' => 'user'], function () {
        Route::get('manage', 'UserController@viewManageUsers')->middleware(['role:0,1,2']);
        Route::get('{id}/affiliates', 'UserController@viewManagersAffiliates')->middleware([
            'role:0,1,2',
            'permissions:' . Permissions::CREATE_MANAGERS
        ]);
	    Route::post('/block-sub-id', 'UserController@blockUserSubId')->middleware(['role:0']);
	    Route::post('/unblock-sub-id', 'UserController@unblockUserSubId')->middleware(['role:0']);
        Route::group(['prefix' => '/{id}/salary', 'middleware' => 'permissions:' . Permissions::EDIT_SALARIES],
            function () {
                Route::get('create', 'SalaryController@showCreate')->name('salary.create');
                Route::post('create', 'SalaryController@create')->name('salary.create');
                Route::get('update', 'SalaryController@showUpdate')->name('salary.update');
                Route::post('update', 'SalaryController@update')->name('salary.update');
            });
        Route::get('{id}/clicks', 'Report\ClickReportController@showUsersClicks')->middleware('role:0,1,2')->name('userClicks');
    });
    Route::group(['prefix' => 'report'], function () {
        Route::get('daily', 'Report\AggregateReportController@show');
        Route::get('offer', 'Report\OfferReportController@show');
        Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD], function () {
            Route::get('advertiser', 'Report\AdvertiserReportController@show');
            Route::get('blacklist', 'Report\BlackListReportController@show');
        });
        Route::get('adjustments', 'Report\AdjustmentsReportController@show')->middleware([
            'permissions:' . Permissions::ADJUST_SALES,
            'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN
        ]);
        Route::get('sale-log', 'Report\ChatLogReportController@affiliate');
        Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN . ',' . Privilege::ROLE_MANAGER],
            function () {
                Route::get('chat-log', 'Report\ChatLogReportController@show');
                Route::get('affiliate', 'Report\EmployeeReportController@show');
                Route::get('chat-log/{userId}', 'Report\ChatLogReportController@admin');
            });
        Route::group(['middleware' => 'role:' . Privilege::ROLE_AFFILIATE], function () {
            Route::get('sub', 'Report\SubReportController@show');
            Route::group(['prefix' => 'payout'], function () {
                Route::get('', 'Report\PayoutReportController@report');
                Route::get('pdf', 'Report\PayoutReportController@invoice');
            });
        });
    });
    Route::group(['prefix' => 'offer'], function () {
        Route::get('manage', 'OfferController@showManage');
        Route::get('{id}/request', 'OfferController@requestOffer')->middleware('role:3');
        Route::group(['middleware' => 'role:0'], function () {
            Route::get('{id}/dupe', 'OfferController@dupe');
            Route::get('{id}/delete', 'OfferController@delete');
        });
        Route::get('{id}/clicks', 'Report\ClickReportController@offerClicks')->middleware('role:0,1,2')->name('offerClicks');
        Route::group(['middleware' => ['permissions:' . Permissions::CREATE_OFFERS]], function () {
            Route::get('create', 'OfferController@showCreate');
            Route::post('create', 'OfferController@create');
            Route::get("edit/{id}", 'OfferController@showEdit');
            Route::get('mass-assign', 'OfferController@showMassAssign');
            Route::post('mass-assign', 'OfferController@massAssign');
            Route::get('assignableUsers', 'OfferController@getAssignableUsers');
            Route::get("assignedUsers/{id}", 'OfferController@getAssignedUsers');
        });
    });
    Route::group(['prefix' => 'email/pools', 'middleware' => "permissions:" . Permissions::EMAIL_POOLS], function () {
        Route::get('', 'EmailPoolController@showAffiliateEmailPools');
        Route::get('{id}/download', 'EmailPoolController@downloadEmailPool');
        Route::get('{id}/claim', 'EmailPoolController@claimEmailPool');
    });
    Route::group(['prefix' => 'sales', 'middleware' => 'permissions:' . Permissions::ADJUST_SALES], function () {
        Route::get('add', 'AdjustmentsController@showAddSaleLog');
        Route::post('add', 'AdjustmentsController@createSale');
        Route::get('affiliate-offers/{id}', 'AdjustmentsController@getAffiliatesOffers');
        Route::get('affiliates', 'AdjustmentsController@getAffiliates');
    });
    Route::group(['prefix' => 'sms'], function () {
        Route::group(['prefix' => 'api'], function () {
            Route::post('messages/send', 'Sms\SmsApiController@sendMessage');
            Route::get('conversations', 'Sms\SmsApiController@getConversations');
            Route::get('conversations/{id}', 'Sms\SmsApiController@getConversation');
            Route::get('conversations/{id}/messages', 'Sms\SmsApiController@getMessages');
            Route::patch('conversations', 'Sms\SmsApiController@patchConversation');
            Route::patch('conversations/{conversationId}/read-new-messages', 'Sms\SmsApiController@readNewMessages');
        });
        Route::get('/', 'Sms\SmsController@getChattingPage')->middleware(['role:' . Privilege::ROLE_AFFILIATE]);
        Route::group(['prefix' => 'client', 'middleware' => 'role:' . Privilege::ROLE_GOD], function () {
            Route::get('add', 'Sms\SmsClientController@create');
            Route::post('add', 'Sms\SmsClientController@store');
            Route::get('edit', 'Sms\SmsClientController@edit');
            Route::post('update', 'Sms\SmsClientController@update');
            Route::post('create', 'Sms\SmsClientController@createSMSWorker');
        });
        Route::get("client", 'Sms\SmsClientController@getUsersClient');
    });
    Route::group(['prefix' => 'chat-log'], function () {
        Route::get('add/{pendingConversionId}', 'ChatLogController@showUploadChatLog');
        Route::post('upload', 'ChatLogController@uploadChatLog');
        Route::get('view/{saleLogId}/{fileName}', 'ChatLogController@getSaleLogImage');
    });
    Route::get("login/{userId}", 'LegacyLoginController@adminLogin');
});


