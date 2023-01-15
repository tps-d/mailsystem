<?php

use App\Http\Controllers\Auth\ApiTokenController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RequireWorkspace;

use App\Repositories\VariableRepository;
use App\Facades\Helper;

Route::get('/test', function(){
 
$recipient_email = "f24cxLFOzq4KaKIyyZI9JtKEbjG0wCmaQzbbK0K+Ohc6eldSuyZi4ts1ZZk";
$variableContent = Helper::authcode($recipient_email);

echo  $variableContent;
exit;

$hashids = new Hashids\Hashids('',6);


$base_str = "cmworld_5345y65.csdvdcsdcsdcsdcsdcsdcsdc";
echo $base_str;
echo "<hr/>";
$base_str = base64_encode($base_str);
$base_str = rtrim($base_str,"=");
echo $base_str;
echo "<hr/>";
$base_str = ShortCode\Reversible::revert($base_str );
echo $base_str;
echo "<hr/>";
$chars = preg_split('//', $base_str, -1, PREG_SPLIT_NO_EMPTY);
print_r($chars);
echo "<hr/>";
$id = $hashids->encode($chars);
echo $id;
exit;
$numbers = $hashids->decode($id);
print_r($numbers);

$base_str = ShortCode\Reversible::convert($base_str);
echo $base_str;
echo "<hr/>";
$base_str = base64_decode($base_str);
echo $base_str;
echo "<hr/>";
/*
$hash = NumberConversion::alphaID($base_str,true);
echo $hash;

echo "<hr/>";
$numbers = NumberConversion::alphaID($hash,false);
print_r($numbers);
*/
exit;

                $encryption = NumberConversion::generateCardByNum(32003, 4);
                echo $encryption;
                echo "<hr/>";
                // 解密;
                $decrypt = NumberConversion::generateNumByCard("gjv5r6vhhvASFBSWW#" );
                echo $decrypt;

/*
                break;
            case 'EXCHANGE_CODE':

                $psa= NumberConversion::encode_pass("woshi ceshi yong de ","123","encode",64);
                echo $psa;
                echo "<hr/>";
                echo NumberConversion::encode_pass($psa,"123",'decode',64);
                // code...
            case 'COUPON_CODE':

                $psa= NumberConversion::alphabet_to_number("cmworldcom");
                echo $psa;
                echo "<hr/>";
                echo NumberConversion::number_to_alphabet($psa);
                */
});

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
            $campaignRouter->get('{id}', 'CampaignsController@show')->name('show');
            $campaignRouter->get('{id}/preview', 'CampaignsController@preview')->name('preview');
            $campaignRouter->put('{id}/send', 'CampaignDispatchController@send')->name('send');
            $campaignRouter->get('{id}/status', 'CampaignsController@status')->name('status');
            $campaignRouter->post('{id}/test', 'CampaignTestController@handle')->name('test');

            $campaignRouter->get(
                '{id}/confirm-delete',
                'CampaignDeleteController@confirm'
            )->name('destroy.confirm');
            $campaignRouter->delete('', 'CampaignDeleteController@destroy')->name('destroy');

            $campaignRouter->get('{id}/duplicate', 'CampaignDuplicateController@duplicate')->name('duplicate');

            $campaignRouter->get('{id}/confirm-cancel', 'CampaignCancellationController@confirm')->name('confirm-cancel');
            $campaignRouter->post('{id}/cancel', 'CampaignCancellationController@cancel')->name('cancel');

            $campaignRouter->get('{id}/report', 'CampaignReportsController@index')->name('reports.index');
            $campaignRouter->get('{id}/report/recipients', 'CampaignReportsController@recipients')
                ->name('reports.recipients');
            $campaignRouter->get('{id}/report/opens', 'CampaignReportsController@opens')->name('reports.opens');
            $campaignRouter->get(
                '{id}/report/clicks',
                'CampaignReportsController@clicks'
            )->name('reports.clicks');
            $campaignRouter->get('{id}/report/unsubscribes', 'CampaignReportsController@unsubscribes')
                ->name('reports.unsubscribes');
            $campaignRouter->get(
                '{id}/report/bounces',
                'CampaignReportsController@bounces'
            )->name('reports.bounces');
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

        // Automations
        $appRouter->name('automations.')->prefix('automations')->namespace('Automations')->group(static function ( Router $servicesRouter ) {
            $servicesRouter->get('/queue/dispatch', 'QueueController@dispatch_jobs')->name('queue.dispatch');
            $servicesRouter->get('/queue/webhook', 'QueueController@webhook_jobs')->name('queue.webhook');
            $servicesRouter->get('/queue/failed', 'QueueController@failed_jobs')->name('queue.failed');
        });

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

