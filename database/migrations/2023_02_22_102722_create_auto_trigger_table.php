<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoTriggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_trigger', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('workspace_id')->index();
                $table->string('name');
                $table->string('from_type');
                $table->unsignedInteger('from_id');
                $table->string('condition')->nullable();
                $table->string('match_content')->nullable();
                $table->unsignedInteger('template_id')->default(0);
                $table->unsignedInteger('status_id')->default(1);
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
        Schema::dropIfExists('auto_trigger');
    }
}
