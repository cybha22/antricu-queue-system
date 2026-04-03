<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20);
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('counter_id')->nullable()->constrained('counters')->onDelete('set null');
            $table->enum('status', ['waiting', 'called', 'served', 'cancelled'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->index(['service_id', 'date']);
            $table->index('status');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
