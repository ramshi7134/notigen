<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notigen_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique();
            $table->text('description')->nullable();
            $table->json('channels');
            $table->json('variables')->nullable();
            $table->json('channel_contents')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notigen_templates');
    }
};
