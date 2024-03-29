<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\Helper;

Route::prefix('v1')->namespace('\App\Http\Controllers\Api')->group( function () {//
    Route::apiResource('campaigns', 'CampaignsController');
    Route::post('campaigns/{id}/send', 'CampaignDispatchController@send')->name('campaigns.send');
    Route::apiResource('subscribers', 'SubscribersController');
    Route::apiResource('tags', 'TagsController');

    Route::apiResource('subscribers.tags', 'SubscriberTagsController')
        ->except(['show', 'update', 'destroy']);
    Route::put('subscribers/{subscriber}/tags', 'SubscriberTagsController@update')
        ->name('subscribers.tags.update');
    Route::delete('subscribers/{subscriber}/tags', 'SubscriberTagsController@destroy')
        ->name('subscribers.tags.destroy');

    Route::apiResource('tags.subscribers', 'TagSubscribersController')
        ->except(['show', 'update', 'destroy']);
    Route::put('tags/{tag}/subscribers', 'TagSubscribersController@update')
        ->name('tags.subscribers.update');
    Route::delete('tags/{tag}/subscribers', 'TagSubscribersController@destroy')
        ->name('tags.subscribers.destroy');

    Route::apiResource('templates', 'TemplatesController');

    Route::post('message/captcha/send', 'Messages\CaptchaDispatchController@send')->name('messages.send_captcha');


});

// Non-auth'd API routes.
Route::prefix('v1/webhooks')->namespace('\App\Http\Controllers\Api\Webhooks')->group(function () {
    Route::post('aws', 'SesWebhooksController@handle')->name('aws');
    Route::post('mailgun', 'MailgunWebhooksController@handle')->name('mailgun');
    Route::post('postmark', 'PostmarkWebhooksController@handle')->name('postmark');
    Route::post('sendgrid', 'SendgridWebhooksController@handle')->name('sendgrid');
    Route::post('mailjet', 'MailjetWebhooksController@handle')->name('mailjet');
    Route::post('postal', 'PostalWebhooksController@handle')->name('postal');
});

Route::post('mail/{platform}/send', '\App\Http\Controllers\Api\Messages\DispatchController@send');


Route::get('v1/ping', '\App\Http\Controllers\Api\PingController@index');


Route::post('v1/platform/{platform}/captcha/fetch', '\App\Http\Controllers\Api\PlatformController@captcha_fetch');

Route::post('v1/platform/{platform}/card/fetch', '\App\Http\Controllers\Api\PlatformController@card_fetch');
Route::get('v1/platform/{platform}/test', '\App\Http\Controllers\Api\PlatformController@test');

Route::post('v1/platform/{platform}/code/check', '\App\Http\Controllers\Api\PlatformController@code_check');
