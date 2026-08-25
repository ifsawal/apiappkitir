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
        Schema::create('a_penjualans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pangkalan2_id')->nullable();
            $table->foreign('pangkalan2_id')->references('id')->on('pangkalan2s')->onUpdate('cascade')->onDelete('restrict');
            $table->integer("jumlah_tabung");
            $table->integer('total_harga');
            $table->enum('status_bayar', ['N', 'Y'])->default('N');
            $table->string('metode_bayar')->comment("cash/transfer")->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('selesai_antar')->nullable()->comment('1=sudah');
            $table->integer('status_create_briva')->nullable()->comment('1=sudah');





            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_penjualans');
    }
};
