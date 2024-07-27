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
        Schema::create('favourites', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
        $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
        // $table->unsignedBigInteger('client_id');
        // $table->unsignedBigInteger('service_id');
        // $table->timestamps();

        // // Define foreign keys
        // $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        // $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

        // Ensure each client can favorite a service only once
        $table->unique(['client_id', 'service_id']);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favourites');
    }
};
