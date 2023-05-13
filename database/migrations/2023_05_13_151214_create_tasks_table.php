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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task', 4095);
            $table->string('solution', 4095);
            $table->string('image')->nullable();
            $table->integer('points');
            $table->string('file_name');
            $table->timestamp('accessible_from')->nullable();;
            $table->timestamp('accessible_to')->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
