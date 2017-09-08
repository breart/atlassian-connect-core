<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddOauthClientTokenColumnToTenantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('plugin.tenant'), function (\Illuminate\Database\Schema\Blueprint $blueprint) {
            $blueprint->string('oauth_client_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('plugin.tenant'), function (\Illuminate\Database\Schema\Blueprint $blueprint) {
            $blueprint->dropColumn('oauth_client_token');
        });
    }
}
