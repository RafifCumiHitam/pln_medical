<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicineStocksTable extends Migration
{
    public function up()
    {
        Schema::create('medicine_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->integer('jumlah_masuk')->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->integer('stok_akhir')->default(0);
            $table->date('tanggal_transaksi');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicine_stocks');
    }
}