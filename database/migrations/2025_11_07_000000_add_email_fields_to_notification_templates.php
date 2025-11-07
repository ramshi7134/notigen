<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->string('unique_key')->after('name')->unique();
            $table->string('subject')->after('description')->nullable();
            $table->json('cc')->after('content')->nullable();
            $table->json('bcc')->after('cc')->nullable();
        });
    }

    public function down()
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn(['unique_key', 'subject', 'cc', 'bcc']);
        });
    }
};
