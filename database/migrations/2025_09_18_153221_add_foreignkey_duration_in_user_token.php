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
        Schema::table('user_token', function (Blueprint $table) {
            $table->unsignedBigInteger('token_id')->default(1)->after('id');
            // definisi foreign key
            $table->foreign('token_id')
                ->references('id')
                ->on('mtoken')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_token', function (Blueprint $table) {
            //
        });
    }
};