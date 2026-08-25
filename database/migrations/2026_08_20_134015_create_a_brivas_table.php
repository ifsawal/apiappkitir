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
        Schema::create('a_brivas', function (Blueprint $table) {
            $table->id();
            $table->json('data')->nullable();
            $table->string('customerNo')->nullable();
            $table->string('virtualAccountNo')->nullable();
            $table->integer('value')->nullable();
            $table->dateTime('trxDateTime')->nullable();
            $table->string('virtualAccountName')->nullable();
            $table->string('trxId')->nullable();
            $table->string('description')->nullable();
            $table->string('sourceAccountVa')->nullable();
            $table->string('tellerId')->nullable();
            $table->unsignedBigInteger('penjualan_id')->nullable();
            $table->foreign('penjualan_id')->references('id')->on('a_penjualans')->onUpdate('cascade')->onDelete('restrict');
            $table->string('kode_transaksi', 64)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_brivas');
    }
};
