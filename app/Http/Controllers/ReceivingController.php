<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\ReceivingNotifyRequest;

use App\Http\Controllers\Controller;

use App\Facades\Mailbox;

class ReceivingController extends Controller
{

    public function notify(ReceivingNotifyRequest $request)
    {
        Mailbox::callMailboxes($request->email());
    }
}
