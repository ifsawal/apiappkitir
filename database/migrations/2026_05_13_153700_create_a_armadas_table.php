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
        Schema::create('a_armadas', function (Blueprint $table) {
            $table->id();
            $table->string('plat');
            $table->string('nama')->nullable;
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('supir_user_id')->nullable();
            $table->foreign('supir_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_armadas');
    }
};
