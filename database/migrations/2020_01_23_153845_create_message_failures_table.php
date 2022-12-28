<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMessageFailuresTable extends Migration
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
        $messages = $this->getTableName('messages');

        Schema::create('sendportal_message_failures', function (Blueprint $table) use ($messages) {
            $table->bigIncrements('id');
            $table->unsignedInteger('message_id');
            $table->string('severity')->nullable()->default(null);
            $table->mediumText('description')->nullable()->default(null);
            $table->timestamp('failed_at')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on($messages);
        });
    }
}
