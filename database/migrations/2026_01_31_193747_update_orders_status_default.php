<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('quoted')->change();
        });

        DB::table('orders')->where('status', 'new')->update(['status' => 'quoted']);
        DB::table('orders')->where('status', 'awaiting_evaluation')->update(['status' => 'needs_review']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('new')->change();
        });

        DB::table('orders')->where('status', 'quoted')->update(['status' => 'new']);
        DB::table('orders')->where('status', 'needs_review')->update(['status' => 'awaiting_evaluation']);
    }
};
