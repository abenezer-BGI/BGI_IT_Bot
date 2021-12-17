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
            $table->string("fidelity_id");
            $table->string("bgi_id");
            $table->string("client_name");
            $table->string("phone_number");
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
