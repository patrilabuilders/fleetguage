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
        Schema::table('subscription_tiers', function (Blueprint $table) {
            $table->integer('max_users')->nullable()->change();
            $table->integer('max_assets')->nullable()->change();
            $table->integer('max_classifications')->nullable()->change();
            $table->integer('max_accounts')->nullable()->change();
            $table->integer('max_sub_accounts_per_account')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_tiers', function (Blueprint $table) {
            $table->integer('max_users')->nullable(false)->change();
            $table->integer('max_assets')->nullable(false)->change();
            $table->integer('max_classifications')->nullable(false)->change();
            $table->integer('max_accounts')->nullable(false)->change();
            $table->integer('max_sub_accounts_per_account')->nullable(false)->change();
        });
    }
};
