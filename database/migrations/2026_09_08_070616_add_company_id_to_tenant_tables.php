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
        $tables = [
            'users',
            'asset_types',
            'assets',
            'utilization_entries',
            'fuel_orders',
            'chargeable_accounts',
            'sub_accounts',
            'sub_account_budgets',
            'public_dashboard_links',
            'accomplishment_registry',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // If it is the users table, we make company_id nullable because of potential system users,
                // but for general multi-tenancy we can constrain it.
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'accomplishment_registry',
            'public_dashboard_links',
            'sub_account_budgets',
            'sub_accounts',
            'chargeable_accounts',
            'fuel_orders',
            'utilization_entries',
            'assets',
            'asset_types',
            'users',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_company_id_foreign']);
                $table->dropColumn('company_id');
            });
        }
    }
};
