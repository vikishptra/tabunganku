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
        Schema::create('detail_transaksi_banks', function (Blueprint $table) {
            $table->string('id')->primary()->charset('utf8');
            $table->string('id_user');
            $table->string('id_va_account');
            $table->bigInteger('amount');
            $table->string('status_trx');
            $table->foreign('id_user')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->foreign('id_va_account')
            ->references('id')
            ->on('account_bank_users')
            ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksis');
    }
};
