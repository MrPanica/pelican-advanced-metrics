<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_metrics_history')) {
            Schema::create('server_metrics_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->float('cpu')->default(0);
                $table->unsignedBigInteger('memory')->default(0);
                $table->unsignedBigInteger('disk')->default(0);
                $table->unsignedBigInteger('network_rx')->default(0);
                $table->unsignedBigInteger('network_tx')->default(0);
                $table->unsignedSmallInteger('players')->default(0);
                $table->float('tickrate')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['server_id', 'created_at']);
                $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_metrics_history');
    }
};
