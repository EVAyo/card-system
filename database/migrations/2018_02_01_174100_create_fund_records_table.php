<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateFundRecordsTable extends Migration { public function up() { Schema::create('fund_records', function (Blueprint $spab94ff) { $spab94ff->increments('id'); $spab94ff->integer('user_id')->index(); $spab94ff->integer('type')->default(\App\FundRecord::TYPE_OUT); $spab94ff->integer('amount'); $spab94ff->integer('balance')->default(0); $spab94ff->integer('order_id')->nullable(); $spab94ff->string('withdraw_id')->nullable(); $spab94ff->string('remark')->nullable(); $spab94ff->timestamps(); }); DB::unprepared('ALTER TABLE `fund_records` CHANGE COLUMN `created_at` `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP;'); } public function down() { Schema::dropIfExists('fund_records'); } }