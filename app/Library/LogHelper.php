<?php
namespace App\Library; class LogHelper { public static function setLogFile($sp91a024) { \Log::getMonolog()->setHandlers(array()); \Log::useDailyFiles(storage_path() . '/logs/' . $sp91a024 . '.log', 0, config('app.log_level')); } }