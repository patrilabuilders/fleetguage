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
            $table->integer('max_classifications')->default(5)->after('max_assets');
            $table->integer('max_accounts')->default(10)->after('max_classifications');
            $table->integer('max_sub_accounts_per_account')->default(10)->after('max_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_tiers', function (Blueprint $table) {
            $table->dropColumn(['max_classifications', 'max_accounts', 'max_sub_accounts_per_account']);
        });
    }
};
