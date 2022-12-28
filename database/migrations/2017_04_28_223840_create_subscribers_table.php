<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSubscribersTable extends Migration
{

    public function getTableName(string $baseName): string
    {
        if (Schema::hasTable("sendportal_{$baseName}")) {
            return "sendportal_{$baseName}";
        }

        if (Schema::hasTable($baseName)) {
            return $baseName;
        }

        throw new RuntimeException('Could not find appropriate table for base name ' . $baseName);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $unsubscribe_event_types = $this->getTableName('unsubscribe_event_types');

        Schema::create('sendportal_subscribers', function (Blueprint $table) use ($unsubscribe_event_types) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->uuid('hash')->unique();
            $table->string('email')->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->unsignedInteger('unsubscribe_event_id')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->foreign('unsubscribe_event_id')->references('id')->on($unsubscribe_event_types);
        });
    }
}
