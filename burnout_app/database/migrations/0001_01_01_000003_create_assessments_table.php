<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('college')->nullable();
            $table->string('year')->nullable();
            $table->text('answers')->nullable();
            $table->integer('Exhaustion')->nullable();
            $table->integer('Disengagement')->nullable();
            $table->string('Burnout_Category')->nullable();
            $table->string('status')->default('new')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->float('confidence')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};

