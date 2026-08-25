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
        Schema::create('a_quotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quota');
            $table->date('tanggal')->comment("di isi tanggal  kitir");
            $table->string('status')->nullable();
            $table->string('jenis')->comment("N atai F normal atau fakultatif")->default('N');
            $table->unsignedBigInteger('input_user_id');
            $table->foreign('input_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->string('perubahan')->nullable()->comment('edit,hapus');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_quotas');
    }
};
