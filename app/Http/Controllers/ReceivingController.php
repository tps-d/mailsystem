<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\ReceivingNotifyRequest;
use App\Http\Controllers\Controller;

//use App\Facades\Mailbox;

class ReceivingController extends Controller
{

    public function notify(ReceivingNotifyRequest $request)
    {
       // Mailbox::callMailboxes($request->email());
    }


    public function telegram_notify(){

        $update = Telegram::commandsHandler(true);

        Log::build([
          'driver' => 'single',
          'path' => storage_path('logs/tg.log'),
        ])->info($update);
        
        // Commands handler method returns an Update object.
        // So you can further process $update object
        // to however you want.

        return 'ok';
    }
}
