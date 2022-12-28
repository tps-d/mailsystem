<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddCancelledCampaignStatus extends Migration
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

    
    public function up()
    {
        $campaign_statuses = $this->getTableName('campaign_statuses');

        DB::table($campaign_statuses)
            ->insert([
                'id' => 5,
                'name' => 'Cancelled',
            ]);
    }
}
