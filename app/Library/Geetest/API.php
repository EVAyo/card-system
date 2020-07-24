<?php
namespace App\Library\Geetest; use Illuminate\Support\Facades\Session; class API { private $geetest_conf = null; public function __construct($sp85fcba) { $this->geetest_conf = $sp85fcba; } public static function get() { $spaaa5c2 = config('services.geetest.id'); $spb2019f = config('services.geetest.key'); if (!strlen($spaaa5c2) || !strlen($spb2019f)) { return null; } $sp4a1d8f = new Lib($spaaa5c2, $spb2019f); $sp9935c9 = time() . rand(1, 10000); $sp2e8268 = $sp4a1d8f->pre_process($sp9935c9); $spf96e67 = json_decode($sp4a1d8f->get_response_str()); Session::put('gt_server', $sp2e8268); Session::put('gt_user_id', $sp9935c9); return $spf96e67; } public static function verify($sp01b76b, $sp92f374, $sp7aeb4a) { $sp4a1d8f = new Lib(config('services.geetest.id'), config('services.geetest.key')); $sp9935c9 = Session::get('gt_user_id'); if (Session::get('gt_server') == 1) { $sp005b78 = $sp4a1d8f->success_validate($sp01b76b, $sp92f374, $sp7aeb4a, $sp9935c9); if ($sp005b78) { return true; } else { return false; } } else { if ($sp4a1d8f->fail_validate($sp01b76b, $sp92f374, $sp7aeb4a)) { return true; } else { return false; } } } }