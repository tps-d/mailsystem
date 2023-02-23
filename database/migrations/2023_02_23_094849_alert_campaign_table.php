<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sendportal_campaigns', function ($table) {
            $table->boolean('is_send_mail')->after('template_id')->default(false);
            $table->boolean('is_send_social')->after('is_send_mail')->default(false);
            $table->unsignedInteger('social_service_id')->after('is_send_social')->default(0);

            $table->dropForeign('sendportal_campaigns_email_service_id_foreign');
        });

        Schema::table('sendportal_messages', function ($table) {
            $table->string('subscriber_type')->after('workspace_id')->index();
            $table->unsignedInteger('recipient_chat_id')->after('source_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sendportal_campaigns', function ($table) {
            $table->dropColumn(['is_send_mail', 'is_send_social','social_service_id']);
        });

        Schema::table('sendportal_messages', function ($table) {
            $table->dropColumn(['subscriber_type', 'recipient_chat_id']);
        });
    }
}
