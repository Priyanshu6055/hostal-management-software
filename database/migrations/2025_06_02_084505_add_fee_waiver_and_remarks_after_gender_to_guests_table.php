<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The class name will be automatically generated and should be unique based on the filename
class AddFeeWaiverAndRemarksAfterGenderToGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            // Add fee_waiver column after 'gender'
            $table->boolean('fee_waiver')
                  ->default(false)
                  ->after('gender')
                  ->comment('Indicates if the guest has a fee waiver (true for Yes, false for No)');

            // Add remarks column after 'fee_waiver'
            $table->text('remarks')
                  ->nullable()
                  ->after('fee_waiver')
                  ->comment('Any additional remarks for the guest');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            // Drop the columns in the reverse order of addition
            $table->dropColumn('remarks');
            $table->dropColumn('fee_waiver');
        });
    }
}