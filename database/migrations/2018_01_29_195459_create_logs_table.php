<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; use Illuminate\Support\Facades\DB; class CreateLogsTable extends Migration { public function up() { Schema::create('logs', function (Blueprint $sp758f0c) { $sp758f0c->increments('id'); $sp758f0c->integer('user_id')->index(); $sp758f0c->string('ip'); $sp758f0c->integer('action')->default(\App\Log::ACTION_LOGIN); $sp758f0c->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); }); } public function down() { Schema::dropIfExists('logs'); } }