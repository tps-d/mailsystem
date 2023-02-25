<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertSocialServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_services', function ($table) {
            $table->string('bot_id')->after('name')->nullable()->default(null);
            $table->string('bot_username')->after('bot_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_services', function ($table) {
            $table->dropColumn(['bot_id','bot_username']);
        });
    }
}
