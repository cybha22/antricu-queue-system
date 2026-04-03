<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'serving', 'served', 'cancelled') DEFAULT 'waiting'");

        Schema::table('queues', function (Blueprint $table) {
            $table->timestamp('serving_at')->nullable()->after('called_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('serving_at');
        });
        
        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'called', 'served', 'cancelled') DEFAULT 'waiting'");
    }
};
