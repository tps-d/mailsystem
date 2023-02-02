<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\InboundEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ReceivingNotifyRequest extends FormRequest
{
    public function validator()
    {
        $validator = Validator::make($this->all(), [
            //'body-mime' => 'required',
            'timestamp' => 'required',
            'token' => 'required',
            'signature' => 'required',
        ]);

/*
        $validator->after(function () {
            $this->verifySignature();
        });
*/
        return $validator;
    }

    public function email()
    {
        return InboundEmail::fromMessage($this->get('body-mime'));
    }

    protected function verifySignature()
    {
        $data = $this->timestamp.$this->token;

        $signature = hash_hmac('sha256', $data, config('mailbox.services.mailgun.key'));

        $signed = hash_equals($this->signature, $signature);

        abort_unless($signed && $this->isFresh($this->timestamp), 401, 'Invalid Mailgun signature or timestamp.');
    }

    protected function isFresh($timestamp): bool
    {
        return now()->subMinutes(2)->lte(Carbon::createFromTimestamp($timestamp));
    }
}