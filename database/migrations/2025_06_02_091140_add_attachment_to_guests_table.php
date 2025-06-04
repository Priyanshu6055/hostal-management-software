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
        Schema::table('guests', function (Blueprint $table) {
            // Add a nullable string column for attachment path
            // It will store the path where the file is saved (e.g., 'attachments/filename.pdf')
            $table->string('attachment_path')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Drop the attachment_path column if rolling back
            $table->dropColumn('attachment_path');
        });
    }
};