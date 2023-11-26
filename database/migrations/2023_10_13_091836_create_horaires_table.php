<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('horaires', function (Blueprint $table) {
            $table->id();
            $table->string('j1')->nullable();
            $table->string('j2')->nullable();
            $table->string('j3')->nullable();
            $table->string('j4')->nullable();
            $table->string('j5')->nullable();
            $table->string('j6')->nullable();
            $table->string('j7')->nullable();
            $table->boolean('status')->nullable()->default(false);
            $table->unsignedBigInteger('pharmacie_id');
            $table->foreign('pharmacie_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horaires');
    }
};
