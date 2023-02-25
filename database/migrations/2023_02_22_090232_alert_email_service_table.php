<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertEmailServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sendportal_email_services', function ($table) {
            $table->string('from_name')->after('name')->nullable()->default(null);
            $table->string('from_email')->after('from_name')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sendportal_email_services', function ($table) {
            $table->dropColumn(['from_name','from_email']);
        });
    }
}
