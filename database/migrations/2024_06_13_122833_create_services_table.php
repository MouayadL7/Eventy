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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->foreignId('categoury_id')->constrained('categouries')->onDelete('cascade');
            $table->string('contact_number');
            $table->string('rating');
            $table->string('location');
            $table->integer('price');
            $table->text('description')->nullable();
            $table->string('profile_image')->nullable(); // Adding profile image column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
