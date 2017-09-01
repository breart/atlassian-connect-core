<?php

use Illuminate\Database\Migrations\Migration;

class CreateTenantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('plugin.tenant'), function (\Illuminate\Database\Schema\Blueprint $blueprint) {
            $blueprint->increments('id');
            $blueprint->string('addon_key');
            $blueprint->string('client_key', 36);
            $blueprint->string('public_key')->nullable();
            $blueprint->string('shared_secret');
            $blueprint->string('server_version', 20);
            $blueprint->string('plugin_version', 20);
            $blueprint->string('base_url');
            $blueprint->string('product_type', 10);
            $blueprint->text('description');
            $blueprint->string('event_type', 20);
            $blueprint->boolean('is_dummy')->default(false);

            $blueprint->rememberToken();
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('plugin.tenant'));
    }
}
