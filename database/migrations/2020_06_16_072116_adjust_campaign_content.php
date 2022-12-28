<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AdjustCampaignContent extends Migration
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
        $campaigns = $this->getTableName('campaigns');

        Schema::table($campaigns, function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }
}
