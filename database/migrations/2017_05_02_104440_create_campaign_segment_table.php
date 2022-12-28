<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCampaignSegmentTable extends Migration
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
        $campaigns = $this->getTableName('campaigns');

        Schema::create('sendportal_campaign_segment', function (Blueprint $table) use ($campaigns, $segments) {
            $table->increments('id');
            $table->unsignedInteger('segment_id');
            $table->unsignedInteger('campaign_id');
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on($segments);
            $table->foreign('campaign_id')->references('id')->on($campaigns);
        });
    }
}
