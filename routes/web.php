<?php

use App\Http\Controllers\Auth\ApiTokenController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RequireWorkspace;
use Illuminate\Support\Facades\Hash;
use App\Repositories\VariableRepository;
use App\Facades\Helper;
use Illuminate\Support\Facades\Storage;

Route::get('/test', function(){

});

Route::fallback( function () {
    abort( 404 );
} );

Route::get('/receiving/{workspace}/notify', '\App\Http\Controllers\ReceivingController@notify');
Route::get('/tg/{token}/webhook', '\App\Http\Controllers\ReceivingController@telegram_notify');

Auth::routes(
    [
        'verify' => config('mailsystem.auth.register', false),
        'register' => config('mailsystem.auth.register', false),
        'reset' => config('mailsystem.auth.password_reset'),
    ]
);


// Auth.
Route::middleware('auth')->namespace('Auth')->group(
    static function (Router $authRouter)
    {
        // Logout.
        $authRouter->get('logout', 'LoginController@logout')->name('logout');

        // Profile.
        $authRouter->middleware('verified')->name('profile.')->prefix('profile')->group(
            static function (
                Router $profileRouter
            ) {
                $profileRouter->get('/', 'ProfileController@show')->name('show');
                $profileRouter->get('/edit', 'ProfileController@edit')->name('edit');
                $profileRouter->put('/', 'ProfileController@update')->name('update');

                $profileRouter->get('/password/reset', 'ProfileController@password_reset')->name('password_reset');
                $profileRouter->put('/password', 'ProfileController@password_update')->name('password_update');
            }
        );

        // API Tokens.
        $authRouter->middleware('verified')->name('api-tokens.')->prefix('api-tokens')->group(static function (Router $apiTokenRouter) {
            $apiTokenRouter->get('/', [ApiTokenController::class, 'index'])->name('index');
            $apiTokenRouter->post('/', [ApiTokenController::class, 'store'])->name('store');
            $apiTokenRouter->delete('{tokenid}', [ApiTokenController::class, 'destroy'])->name('destroy');
        });
    }
);

// Workspace User Management.
Route::namespace('Workspaces')
    ->middleware(['auth', 'verified'])
    ->name('users.')
    ->prefix('users')
    ->group(
        static function (Router $workspacesRouter)
        {
            $workspacesRouter->get('/', 'WorkspaceUsersController@index')->name('index');
            $workspacesRouter->delete('{userId}', 'WorkspaceUsersController@destroy')->name('destroy');

            // Invitations.
            $workspacesRouter->name('invitations.')->prefix('invitations')
                ->group(
                    static function (Router $invitationsRouter)
                    {
                        $invitationsRouter->post('/', 'WorkspaceInvitationsController@store')->name('store');
                        $invitationsRouter->delete('{invitation}', 'WorkspaceInvitationsController@destroy')
                            ->name('destroy');
                    }
                );
        }
    );

// Workspace Management.
Route::namespace('Workspaces')->middleware(
    [
        'auth',
        'verified'
    ]
)->group(
    static function (Router $workspaceRouter)
    {
        $workspaceRouter->resource('workspaces', 'WorkspacesController')->except(
            [
                'create',
                'show',
                'destroy',
            ]
        );

        // Workspace Switching.
        $workspaceRouter->get('workspaces/{workspace}/switch', 'SwitchWorkspaceController@switch')
            ->name('workspaces.switch');

        // Invitations.
        $workspaceRouter->post('workspaces/invitations/{invitation}/accept', 'PendingInvitationController@accept')
            ->name('workspaces.invitations.accept');
        $workspaceRouter->post('workspaces/invitations/{invitation}/reject', 'PendingInvitationController@reject')
            ->name('workspaces.invitations.reject');
    }
);

Route::middleware(['auth', 'verified','locale'])->group(function (){
    Route::namespace('\App\Http\Controllers')->group(static function (  Router $appRouter) {

        // Dashboard.
        $appRouter->get('/', 'DashboardController@index')->name('dashboard');

        // Campaigns.
        $appRouter->resource('campaigns', 'Campaigns\CampaignsController')->except(['show', 'destroy']);
        $appRouter->name('campaigns.')->prefix('campaigns')->namespace('Campaigns')->group(static function ( Router $campaignRouter ) {
            $campaignRouter->get('sent', 'CampaignsController@sent')->name('sent');
            $campaignRouter->get('listen', 'CampaignsController@listen')->name('listen');
            $campaignRouter->get('delayed', 'CampaignsController@delayed')->name('delayed');
            $campaignRouter->get('{id}', 'CampaignsController@show')->name('show');
            $campaignRouter->get('{id}/preview', 'CampaignsController@preview')->name('preview');
            $campaignRouter->put('{id}/send', 'CampaignDispatchController@send')->name('send');
            $campaignRouter->get('{id}/status', 'CampaignsController@status')->name('status');
            $campaignRouter->post('{id}/test/email', 'CampaignTestController@handle_mail')->name('test_mail');
            $campaignRouter->post('{id}/test/social', 'CampaignTestController@handle_social')->name('test_social');

            $campaignRouter->get('{id}/confirm-delete','CampaignDeleteController@confirm')->name('destroy.confirm');
            $campaignRouter->delete('', 'CampaignDeleteController@destroy')->name('destroy');

           // $campaignRouter->get('{id}/duplicate', 'CampaignDuplicateController@duplicate')->name('duplicate');

            $campaignRouter->get('{id}/confirm-cancel', 'CampaignCancellationController@confirm')->name('confirm-cancel');
            $campaignRouter->post('{id}/cancel', 'CampaignCancellationController@cancel')->name('cancel');

            $campaignRouter->get('{id}/report', 'CampaignReportsController@index')->name('reports.index');
            $campaignRouter->get('{id}/report/recipients', 'CampaignReportsController@recipients')->name('reports.recipients');
            $campaignRouter->get('{id}/report/opens', 'CampaignReportsController@opens')->name('reports.opens');
            $campaignRouter->get('{id}/report/clicks','CampaignReportsController@clicks')->name('reports.clicks');
            $campaignRouter->get('{id}/report/unsubscribes', 'CampaignReportsController@unsubscribes')->name('reports.unsubscribes');
            $campaignRouter->get('{id}/report/bounces','CampaignReportsController@bounces')->name('reports.bounces');
        });

        // Messages.
        $appRouter->name('messages.')->prefix('messages')->group(static function (Router $messageRouter) {
            $messageRouter->get('/', 'MessagesController@index')->name('index');
            $messageRouter->get('draft', 'MessagesController@draft')->name('draft');
            $messageRouter->get('{id}/show', 'MessagesController@show')->name('show');
            $messageRouter->post('send', 'MessagesController@send')->name('send');
            $messageRouter->delete('{id}/delete', 'MessagesController@delete')->name('delete');
            $messageRouter->post('send-selected', 'MessagesController@sendSelected')->name('send-selected');
        });

        // Email Services.
        $appRouter->name('email_services.')->prefix('email-services')->namespace('EmailServices')->group(static function ( Router $servicesRouter ) {
            $servicesRouter->get('/', 'EmailServicesController@index')->name('index');
            $servicesRouter->get('create', 'EmailServicesController@create')->name('create');
            $servicesRouter->get('type/{id}', 'EmailServicesController@emailServicesTypeAjax')->name('ajax');
            $servicesRouter->post('/', 'EmailServicesController@store')->name('store');
            $servicesRouter->get('{id}/edit', 'EmailServicesController@edit')->name('edit');
            $servicesRouter->put('{id}', 'EmailServicesController@update')->name('update');
            $servicesRouter->delete('{id}', 'EmailServicesController@delete')->name('delete');

            $servicesRouter->get('{id}/test', 'TestEmailServiceController@create')->name('test.create');
            $servicesRouter->post('{id}/test', 'TestEmailServiceController@store')->name('test.store');
        });

        // Tags.
        $appRouter->resource('tags', 'Tags\TagsController')->except(['show']);
        $appRouter->resource('templates', 'TemplatesController');

        // Subscribers.
        $appRouter->name('subscribers.')->prefix('subscribers')->namespace('Subscribers')->group(static function ( Router $subscriberRouter ) {
            $subscriberRouter->get('export', 'SubscribersController@export')->name('export');
            $subscriberRouter->get('import', 'SubscribersImportController@show')->name('import');
            $subscriberRouter->post('import', 'SubscribersImportController@store')->name('import.store');
        });
        $appRouter->resource('subscribers', 'Subscribers\SubscribersController');

        // Templates.
        $appRouter->resource('templates', 'TemplatesController')->except(['show']);

        // Variable.
        $appRouter->resource('variable', 'Variable\VariableController')->except(['show']);

        // Socialapp
        $appRouter->name('social_services.')->prefix('social-services')->namespace('SocialServices')->group(static function ( Router $servicesRouter ) {
            $servicesRouter->get('/', 'SocialServicesController@index')->name('index');
            $servicesRouter->get('create', 'SocialServicesController@create')->name('create');
            $servicesRouter->get('type/{id}', 'SocialServicesController@socialServicesTypeAjax')->name('ajax');
            $servicesRouter->post('/', 'SocialServicesController@store')->name('store');
            $servicesRouter->get('{id}/edit', 'SocialServicesController@edit')->name('edit');
            $servicesRouter->put('{id}', 'SocialServicesController@update')->name('update');
            $servicesRouter->delete('{id}', 'SocialServicesController@delete')->name('delete');

            $servicesRouter->get('{id}/test', 'SocialServicesController@test')->name('test');
        });

        // Socialusers
        $appRouter->resource('socialusers', 'SocialUsers\SocialUserController');

        // Automations
        $appRouter->resource('automations', 'Automations\AutomationsController')->except(['show', 'destroy']);
        $appRouter->name('automations.')->prefix('automations')->namespace('Automations')->group(static function ( Router $automationsRouter ) {

            $automationsRouter->get('{id}/status', 'AutomationsController@status')->name('status');
            $automationsRouter->post('{id}/start', 'AutomationsController@start')->name('start');
            $automationsRouter->post('{id}/stop', 'AutomationsController@stop')->name('stop');

            $automationsRouter->get( '{id}/confirm-delete', 'AutomationsController@confirm' )->name('destroy.confirm');
            $automationsRouter->delete('', 'AutomationsController@destroy')->name('destroy');

        });

        // Autotrigger
        $appRouter->resource('autotrigger', 'Automations\AutotriggerController')->except(['index','create','show', 'destroy']);
        $appRouter->name('autotrigger.')->prefix('autotrigger')->namespace('Automations')->group(static function ( Router $automationsRouter ) {
            $automationsRouter->get('{type}/index', 'AutotriggerController@index')->name('index');
            $automationsRouter->get('{type}/create', 'AutotriggerController@create')->name('create');
            $automationsRouter->get('{id}/status', 'AutotriggerController@status')->name('status');
            $automationsRouter->post('{id}/active', 'AutotriggerController@active')->name('active');
            $automationsRouter->post('{id}/cancel', 'AutotriggerController@cancel')->name('cancel');

            $automationsRouter->get( '{id}/confirm-delete', 'AutotriggerController@confirm' )->name('destroy.confirm');
            $automationsRouter->delete('', 'AutotriggerController@destroy')->name('destroy');

        });

        // Queue
        $appRouter->name('queue.')->prefix('queue')->namespace('Queue')->group(static function ( Router $automationsRouter ) {
            $automationsRouter->get('/queue/dispatch', 'QueueController@dispatch_jobs')->name('dispatch');
            $automationsRouter->get('/queue/webhook', 'QueueController@webhook_jobs')->name('webhook');
            $automationsRouter->get('/queue/failed', 'QueueController@failed_jobs')->name('failed');

            $automationsRouter->post('/queue/failed/{id}/retry', 'QueueController@retry')->name('failed.retry');
            $automationsRouter->post('/queue/failed/{id}/delete', 'QueueController@delete')->name('failed.delete');
        });

        $appRouter->get('/platform/card/list', '\App\Http\Controllers\Api\PlatformController@card_list');
    });
});


Route::namespace('\App\Http\Controllers')->group(static function ( Router $appRouter) {
    // Subscriptions
    $appRouter->name('subscriptions.')->namespace('Subscriptions')->prefix('subscriptions')->group(static function (
        Router $subscriptionController
    ) {
        $subscriptionController->get('unsubscribe/{messageHash}', 'SubscriptionsController@unsubscribe')
            ->name('unsubscribe');
        $subscriptionController->get(
            'subscribe/{messageHash}',
            'SubscriptionsController@subscribe'
        )->name('subscribe');
        $subscriptionController->put(
            'subscriptions/{messageHash}',
            'SubscriptionsController@update'
        )->name('update');
    });

    // Webview.
    $appRouter->name('webview.')->prefix('webview')->namespace('Webview')->group(static function (
        Router $webviewRouter
    ) {
        $webviewRouter->get('{messageHash}', 'WebviewController@show')->name('show');
    });
});

