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
        Schema::create('pharmacie_user_agent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacie_id');
            $table->unsignedBigInteger('agentPharmacie_id');
            $table->timestamps();
            $table->foreign('pharmacie_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('agentPharmacie_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacie_user_agent');
    }
};
