<?php

use Illuminate\Support\Facades\DB;
use App\Models\EmailServiceType;
use Illuminate\Database\Migrations\Migration;

class AddMailjetEmailService extends Migration
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
        $email_service_types = $this->getTableName('email_service_types');

        DB::table($email_service_types)
            ->insert(
                [
                    'id' => EmailServiceType::MAILJET,
                    'name' => 'Mailjet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }
}
