<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialUserTable extends Migration
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

        Schema::create('social_users', function (Blueprint $table) use ($unsubscribe_event_types) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->uuid('hash')->unique();
            $table->unsignedInteger('chat_id')->default(0);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->unsignedInteger('unsubscribe_event_id')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->foreign('unsubscribe_event_id')->references('id')->on($unsubscribe_event_types);
        });
    }

    public function down(){
        Schema::dropIfExists('social_users');
    }
}
