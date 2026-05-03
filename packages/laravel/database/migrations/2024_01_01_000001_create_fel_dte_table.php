<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_fel_dte_table
 * Stores every certified, failed, pending, and cancelled DTE.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('fel_dte', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->index();
            $table->string('type', 10)->comment('DTE type code: FACT, NCRE, FPEQ, etc.');
            $table->string('serie', 20)->nullable();
            $table->string('numero', 30)->nullable();
            $table->string('recipient_nit', 20)->nullable()->index();
            $table->string('recipient_name', 255)->nullable();
            $table->uuid('idempotency_key')->unique();
            $table->enum('status', ['issued', 'failed', 'pending', 'cancelled'])
                  ->default('pending')
                  ->index();
            $table->text('xml_certified')->nullable();
            $table->json('infile_response')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fel_dte');
    }
};
