<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkAPITable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.api.settings'), function (Blueprint $table) {
            $table->uuid('id');
            $table->nullableUuidMorphs('host');
            $table->string('serial')->nullable();
            $table->string('type')->nullable();
            $table->string('app_id')->nullable();
            $table->string('app_token')->nullable();
            $table->string('app_key')->nullable();
            $table->string('app_secret')->nullable();
            $table->string('function_id')->nullable();
            $table->string('hash_key')->nullable();
            $table->string('hash_iv')->nullable();
            $table->string('url_notify')->nullable();
            $table->string('url_return')->nullable();
            $table->string('url_success')->nullable();
            $table->string('url_cancel')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->primary('id');
            $table->index('serial');
            $table->index('is_enabled');
        });
        if (!config('wk-api.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.api.settings_lang'), function (Blueprint $table) {
                $table->uuid('id');
                $table->uuidMorphs('morph');
                $table->uuid('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                $table->primary('id');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.api.settings_lang'));
        Schema::dropIfExists(config('wk-core.table.api.settings'));
    }
}
