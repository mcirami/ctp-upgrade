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
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LanderController;
use App\Http\Controllers\LegacyLoginController;
use App\Http\Controllers\RelevanceReactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\Report\ClickReportController;
use App\Http\Controllers\Report\AggregateReportController;
use App\Http\Controllers\Report\OfferReportController;
use App\Http\Controllers\Report\AdvertiserReportController;
use App\Http\Controllers\Report\BlackListReportController;
use App\Http\Controllers\Report\AdjustmentsReportController;
use App\Http\Controllers\Report\ChatLogReportController;
use App\Http\Controllers\Report\EmployeeReportController;
use App\Http\Controllers\Report\SubReportController;
use App\Http\Controllers\Report\PayoutReportController;

Route::get('/', [IndexController::class, 'index']);
Route::post('/', [IndexController::class, 'index']);
Route::any('/resources/landers/{subDomain}/{asset}', [LanderController::class, 'getAsset'])->where('asset', '.*');
Route::get('/logout', [LegacyLoginController::class, 'logout']);
Route::post('email/incoming', [RelevanceReactorController::class, 'incomingEmail']);
Route::post('email/incoming/distribute', [RelevanceReactorController::class, 'distributeEmail']);
Route::group(['middleware' => 'legacy.auth'], function () {
    Route::get('dashboard', [DashboardController::class, 'home']);
    Route::group(['prefix' => 'user'], function () {
        Route::get('manage', [UserController::class, 'viewManageUsers'])->middleware(['role:0,1,2']);
        Route::get('{id}/affiliates', [UserController::class, 'viewManagersAffiliates'])->middleware([
            'role:0,1,2',
            'permissions:' . Permissions::CREATE_MANAGERS
        ]);
	    Route::post('/block-sub-id', [UserController::class, 'blockUserSubId'])->middleware(['role:0']);
	    Route::post('/unblock-sub-id', [UserController::class, 'unblockUserSubId'])->middleware(['role:0']);
        Route::group(['prefix' => '/{id}/salary', 'middleware' => 'permissions:' . Permissions::EDIT_SALARIES],
            function () {
                Route::get('create', [SalaryController::class, 'showCreate'])->name('salary.create');
                Route::post('create', [SalaryController::class, 'create'])->name('salary.create');
                Route::get('update', [SalaryController::class, 'showUpdate'])->name('salary.update');
                Route::post('update', [SalaryController::class, 'update'])->name('salary.update');
            });
        Route::get('{id}/clicks', [ClickReportController::class, 'showUsersClicks'])->middleware('role:0,1,2')->name('userClicks');
    });
    Route::group(['prefix' => 'report'], function () {
        Route::get('daily', [AggregateReportController::class, 'show']);
        Route::get('offer', [OfferReportController::class, 'show']);
        Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD], function () {
            Route::get('advertiser', [AdvertiserReportController::class, 'show']);
            Route::get('blacklist', [BlackListReportController::class, 'show']);
        });
        Route::get('adjustments', [AdjustmentsReportController::class, 'show'])->middleware([
            'permissions:' . Permissions::ADJUST_SALES,
            'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN
        ]);
        Route::get('sale-log', [ChatLogReportController::class, 'affiliate']);
        Route::group(['middleware' => 'role:' . Privilege::ROLE_GOD . ',' . Privilege::ROLE_ADMIN . ',' . Privilege::ROLE_MANAGER],
            function () {
                Route::get('chat-log', [ChatLogReportController::class, 'show']);
                Route::get('affiliate', [EmployeeReportController::class,'show']);
                Route::get('chat-log/{userId}', [ChatLogReportController::class, 'admin']);
            });
        Route::group(['middleware' => 'role:' . Privilege::ROLE_AFFILIATE], function () {
            Route::get('sub', [SubReportController::class,'show']);
            Route::group(['prefix' => 'payout'], function () {
                Route::get('', [PayoutReportController::class, 'report']);
                Route::get('pdf', [PayoutReportController::class, 'invoice']);
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


