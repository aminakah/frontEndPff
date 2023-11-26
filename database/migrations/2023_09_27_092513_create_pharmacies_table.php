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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('photo')->nullable();
            $table->string('adresse');
            $table->integer('telephone');
            $table->integer('fax');
            $table->double('latitude', 10, 6)->nullable();
            $table->double('longitude', 10, 6)->nullable();
            $table->unsignedBigInteger('proprietaire_id');
            $table->unsignedBigInteger('quartier_id');
            $table->foreign('proprietaire_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quartier_id')->references('id')->on('quartiers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
