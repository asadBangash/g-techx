<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_invoices') && !Schema::hasColumn('sales_invoices', 'currency_code')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('currency_code', 3)->default('USD')->after('notes');
                $table->decimal('exchange_rate', 15, 6)->default(1)->after('currency_code');
            });
        }

        if (Schema::hasTable('purchase_invoices') && !Schema::hasColumn('purchase_invoices', 'currency_code')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->string('currency_code', 3)->default('USD')->after('notes');
                $table->decimal('exchange_rate', 15, 6)->default(1)->after('currency_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('sales_invoices', 'exchange_rate')) {
                    $table->dropColumn('exchange_rate');
                }
                if (Schema::hasColumn('sales_invoices', 'currency_code')) {
                    $table->dropColumn('currency_code');
                }
            });
        }

        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoices', 'exchange_rate')) {
                    $table->dropColumn('exchange_rate');
                }
                if (Schema::hasColumn('purchase_invoices', 'currency_code')) {
                    $table->dropColumn('currency_code');
                }
            });
        }
    }
};
