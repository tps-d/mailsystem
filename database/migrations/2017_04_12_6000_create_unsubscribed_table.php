<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use App\Models\UnsubscribeEventType;
use Illuminate\Support\Facades\Schema;

class CreateUnsubscribedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_unsubscribe_event_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $types = [
            UnsubscribeEventType::BOUNCE => 'Bounce',
            UnsubscribeEventType::COMPLAINT => 'Complaint',
            UnsubscribeEventType::MANUAL_BY_ADMIN => 'Manual by Admin',
            UnsubscribeEventType::MANUAL_BY_SUBSCRIBER => 'Manual by Subscriber',
        ];

        foreach ($types as $id => $name) {
            DB::table('sendportal_unsubscribe_event_types')->insert([
                'id' => $id,
                'name' => $name
            ]);
        }
    }
}
