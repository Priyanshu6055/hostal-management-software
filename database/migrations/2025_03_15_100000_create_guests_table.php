<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestsTable extends Migration
{

    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('gender');
            $table->string('scholar_no')->unique();
            $table->string('fathers_name');
            $table->string('mothers_name');
            $table->unsignedTinyInteger('months')->default(3)->comment('Duration of stay in months');
            $table->string('local_guardian_name');
            $table->string('emergency_no');
            $table->string('room_preference');
            $table->string('food_preference');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guests');
    }

}
