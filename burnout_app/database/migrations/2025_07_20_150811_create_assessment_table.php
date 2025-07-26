<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->json('answers');
            $table->enum('overall_risk', ['low', 'moderate', 'high'])->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->float('confidence')->nullable();
            $table->integer('exhaustion_score')->nullable();
            $table->integer('disengagement_score')->nullable();
            $table->string('name')->nullable();
            $table->integer('age');
            $table->string('gender');
            $table->string('program');
            $table->string('year_level');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessments');
    }
};