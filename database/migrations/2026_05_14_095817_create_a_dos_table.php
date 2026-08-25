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
        Schema::create('a_dos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quota_id');
            $table->foreign('quota_id')->references('id')->on('a_quotas')->onUpdate('cascade')->onDelete('restrict');
            $table->date('tanggal_muat');
            $table->date('tanggal_sampai')->nullable();
            $table->string('tujuan')->nullable()->comment('SPBE');
            $table->integer('jumlah')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('Mobil')->nullable();
            $table->string('status')->nullable()->comment('belum,diproses,selesai');

            $table->unsignedBigInteger('armada_id')->nullable();
            $table->foreign('armada_id')->references('id')->on('a_armadas')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('supir_user_id');
            $table->foreign('supir_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('input_user_id');
            $table->foreign('input_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->integer('selesai')->nullable()->comment('1=sudah,0=belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_dos');
    }
};
