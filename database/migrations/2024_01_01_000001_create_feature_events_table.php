<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feature_events', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type'); // e.g., 'viewed', 'clicked', 'completed_purchase'
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_events');
    }
};
