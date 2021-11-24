<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackAndRootPathOnBotStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bot_status',function (Blueprint $table){
            if(!Schema::hasColumn('bot_status','back_path'))
            {
                $table->string("back_path");
            }
            if(!Schema::hasColumn('bot_status','root_path'))
            {
                $table->string("root_path");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bot_status',function (Blueprint $table){
            if(Schema::hasColumn('bot_status','back_path'))
            {
                $table->dropColumn("back_path");
            }
            if(Schema::hasColumn('bot_status','root_path'))
            {
                $table->dropColumn("root_path");
            }
        });
    }
}
