<?php
namespace App\Http\Controllers\Admin; use App\Library\Helper; use Carbon\Carbon; use function foo\func; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use App\Library\Response; class Pay extends Controller { function get(Request $sp7f7104) { $spbec213 = \App\Pay::query(); $sp229dc8 = $sp7f7104->input('enabled'); if (strlen($sp229dc8)) { $spbec213->whereIn('enabled', explode(',', $sp229dc8)); } $spad233c = $sp7f7104->input('search', false); $spaefd46 = $sp7f7104->input('val', false); if ($spad233c && $spaefd46) { if ($spad233c == 'simple') { return Response::success($spbec213->get(array('id', 'name', 'enabled', 'comment'))); } elseif ($spad233c == 'id') { $spbec213->where('id', $spaefd46); } else { $spbec213->where($spad233c, 'like', '%' . $spaefd46 . '%'); } } $spa229b5 = $spbec213->get(); return Response::success(array('list' => $spa229b5, 'urls' => array('url' => config('app.url'), 'url_api' => config('app.url_api')))); } function stat(Request $sp7f7104) { $this->validate($sp7f7104, array('day' => 'required|integer|between:1,30')); $sp2c608d = (int) $sp7f7104->input('day'); if ($sp2c608d === 30) { $sp5976d4 = Carbon::now()->addMonths(-1); } else { $sp5976d4 = Carbon::now()->addDays(-$sp2c608d); } $spa229b5 = $this->authQuery($sp7f7104, \App\Order::class)->where(function ($spbec213) { $spbec213->where('status', \App\Order::STATUS_PAID)->orWhere('status', \App\Order::STATUS_SUCCESS); })->where('paid_at', '>=', $sp5976d4)->with(array('pay' => function ($spbec213) { $spbec213->select(array('id', 'name')); }))->groupBy('pay_id')->selectRaw('`pay_id`,COUNT(*) as "count",SUM(`paid`) as "sum"')->get()->toArray(); $sp6e55ba = array(); foreach ($spa229b5 as $spe4455f) { if (isset($spe4455f['pay']) && isset($spe4455f['pay']['name'])) { $spf12b21 = $spe4455f['pay']['name']; } else { $spf12b21 = '未知方式#' . $spe4455f['pay_id']; } $sp6e55ba[$spf12b21] = array((int) $spe4455f['count'], (int) $spe4455f['sum']); } return Response::success($sp6e55ba); } function edit(Request $sp7f7104) { $this->validate($sp7f7104, array('id' => 'required|integer', 'name' => 'required|string', 'driver' => 'required|string', 'way' => 'required|string', 'config' => 'required|string', 'fee_system' => 'required|numeric')); $sp5c2227 = \App\Pay::find((int) $sp7f7104->post('id')); if (!$sp5c2227) { $sp5c2227 = new \App\Pay(); } $sp5c2227->name = $sp7f7104->post('name'); $sp5c2227->comment = $sp7f7104->post('comment'); $sp5c2227->driver = $sp7f7104->post('driver'); $sp5c2227->way = $sp7f7104->post('way'); $sp5c2227->config = $sp7f7104->post('config'); $sp5c2227->enabled = (int) $sp7f7104->post('enabled'); $sp5c2227->fee_system = $sp7f7104->post('fee_system'); $sp5c2227->saveOrFail(); return Response::success(); } function comment(Request $sp7f7104) { $this->validate($sp7f7104, array('id' => 'required|integer')); $sp1beabb = (int) $sp7f7104->post('id'); $sp5c2227 = \App\Pay::findOrFail($sp1beabb); $sp5c2227->comment = $sp7f7104->post('comment'); $sp5c2227->save(); return Response::success(); } function fee_system(Request $sp7f7104) { $this->validate($sp7f7104, array('id' => 'required|integer')); $sp1beabb = (int) $sp7f7104->post('id'); $sp5c2227 = \App\Pay::findOrFail($sp1beabb); $sp5c2227->fee_system = $sp7f7104->post('fee_system'); $sp5c2227->saveOrFail(); return Response::success(); } function enable(Request $sp7f7104) { $this->validate($sp7f7104, array('ids' => 'required|string', 'enabled' => 'required|integer|between:0,3')); $sp70cf7b = $sp7f7104->post('ids'); $sp229dc8 = (int) $sp7f7104->post('enabled'); \App\Pay::whereIn('id', explode(',', $sp70cf7b))->update(array('enabled' => $sp229dc8)); \App\Pay::flushCache(); return Response::success(); } function delete(Request $sp7f7104) { $this->validate($sp7f7104, array('ids' => 'required|string')); $sp70cf7b = $sp7f7104->post('ids'); \App\Pay::whereIn('id', explode(',', $sp70cf7b))->delete(); \App\Pay::flushCache(); return Response::success(); } }