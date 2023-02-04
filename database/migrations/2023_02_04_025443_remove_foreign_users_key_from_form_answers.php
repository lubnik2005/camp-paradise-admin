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
        Schema::table('formAnswers', function (Blueprint $table) {
            $table->dropForeign('formanswers_attendee_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formAnswers', function (Blueprint $table) {
            $table->foreign('attendee_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
