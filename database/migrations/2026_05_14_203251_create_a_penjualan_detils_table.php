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
        Schema::create('a_penjualan_detils', function (Blueprint $table) {
            $table->id();
            $table->integer("jumlah_tabung");
            $table->unsignedBigInteger('do_id')->nullable();
            $table->foreign('do_id')->references('id')->on('a_dos')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('penjualan_id')->nullable();
            $table->foreign('penjualan_id')->references('id')->on('a_penjualans')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('armada_id')->nullable();
            $table->foreign('armada_id')->references('id')->on('a_armadas')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('input_user_id');
            $table->foreign('input_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_penjualan_detils');
    }
};
