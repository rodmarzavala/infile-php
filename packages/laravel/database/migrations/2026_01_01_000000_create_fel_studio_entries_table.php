<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('fel_studio_entries')) {
            Schema::create('fel_studio_entries', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->nullable();
                $table->string('serie')->nullable();
                $table->string('numero')->nullable();
                $table->string('dte_type')->nullable();
                $table->string('recipient_tax_id')->nullable();
                $table->string('idempotency_key')->nullable();
                $table->string('status')->default('issued');
                $table->json('payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fel_studio_entries');
    }
};
