<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateELeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_leaders', function (Blueprint $table) {
            $table->id();
            $table->string("fidelity_id")->nullable();
            $table->string("bgi_id")->nullable();
            $table->string("client_name")->nullable();
            $table->string("phone_number")->nullable();
            $table->foreignId("user_id");
            $table->softDeletes();
            $table->foreignId("deleted_by")->nullable();
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
        Schema::dropIfExists('e_leader');
    }
}
