<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use App\Models\SocialServiceType;
use Illuminate\Support\Facades\Schema;

class CreateSocialServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_service_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->seedSocialServiceTypes();

        Schema::create('social_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->string('name')->nullable();
            $table->unsignedInteger('type_id');
            $table->mediumText('settings');
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('social_service_types');
        });
    }

    protected function seedSocialServiceTypes()
    {
        $serviceTypes = [
            [
                'id' => SocialServiceType::TELEGRAM,
                'name' => 'Telegram'
            ]
        ];

        foreach ($serviceTypes as $type) {
            DB::table('social_service_types')
                ->insert(
                    $type + [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
        }
    }
}
