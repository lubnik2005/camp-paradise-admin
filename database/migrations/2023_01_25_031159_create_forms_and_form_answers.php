<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('required');
            $table->json('questions');
            $table->timestamps();
        });
        Schema::create('formAnswers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->foreign('form_id')
                ->references('id')
                ->on('forms')
                ->restrictOnDelete();
            $table->unsignedBigInteger('attendee_id');
            $table->foreign('attendee_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
            $table->json('answers');
            $table->dateTime('signed_on');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formAnswers');
        Schema::dropIfExists('forms');
    }
};
