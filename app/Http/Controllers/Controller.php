<?php
namespace App\Http\Controllers; use App\System; use Illuminate\Foundation\Bus\DispatchesJobs; use Illuminate\Routing\Controller as BaseController; use Illuminate\Foundation\Validation\ValidatesRequests; use Illuminate\Foundation\Auth\Access\AuthorizesRequests; use Illuminate\Http\Request; class Controller extends BaseController { use AuthorizesRequests, DispatchesJobs, ValidatesRequests; function authQuery(Request $sp16eb02, $spc65d03, $sp999592 = 'user_id', $sp8346e5 = 'user_id') { return $spc65d03::where($sp999592, \Auth::id()); } protected function getUserId(Request $sp16eb02, $sp8346e5 = 'user_id') { return \Auth::id(); } protected function getUserIdOrFail(Request $sp16eb02, $sp8346e5 = 'user_id') { $spaa7e02 = self::getUserId($sp16eb02, $sp8346e5); if ($spaa7e02) { return $spaa7e02; } else { throw new \Exception('参数缺少 ' . $sp8346e5); } } protected function getUser(Request $sp16eb02) { return \Auth::getUser(); } protected function checkIsInMaintain() { if ((int) System::_get('maintain') === 1) { $sp3a14d3 = System::_get('maintain_info'); echo view('message', array('title' => '维护中', 'message' => $sp3a14d3)); die; } } protected function msg($spa04af2, $spbe30f5 = null, $spddfa55 = null) { return view('message', array('message' => $spa04af2, 'title' => $spbe30f5, 'exception' => $spddfa55)); } }