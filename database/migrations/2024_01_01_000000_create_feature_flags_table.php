<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->json('rules')->nullable();
            $table->timestamps();
            
            $table->index(['name', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_flags');
    }
};