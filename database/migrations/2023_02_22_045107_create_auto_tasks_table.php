<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('workspace_id')->index();
                $table->unsignedInteger('campaign_id')->index();
                $table->unsignedInteger('type_id');
                $table->string('expression')->nullable();
                $table->unsignedInteger('status_id')->default(1);
                $table->timestamp('last_ran_at')->nullable();
                $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auto_tasks');
    }
}
