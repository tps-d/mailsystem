<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSegmentSubscriberTable extends Migration
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
        $segments = $this->getTableName('segments');
        $subscribers = $this->getTableName('subscribers');

        Schema::create('sendportal_segment_subscriber', function (Blueprint $table) use ($segments, $subscribers) {
            $table->increments('id');
            $table->unsignedInteger('segment_id');
            $table->unsignedInteger('subscriber_id');
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on($segments);
            $table->foreign('subscriber_id')->references('id')->on($subscribers);
        });
    }
}
