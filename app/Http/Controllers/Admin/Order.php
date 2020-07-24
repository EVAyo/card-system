<?php
namespace App\Http\Controllers\Admin; use App\Library\FundHelper; use App\Library\Helper; use Carbon\Carbon; use Illuminate\Database\Eloquent\Relations\Relation; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use App\Library\Response; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Log; class Order extends Controller { public function delete(Request $sp0aae4c) { $this->validate($sp0aae4c, array('ids' => 'required|string', 'income' => 'required|integer', 'balance' => 'required|integer')); $spcffd00 = $sp0aae4c->post('ids'); $spd32bb5 = (int) $sp0aae4c->post('income'); $sp705266 = (int) $sp0aae4c->post('balance'); \App\Order::whereIn('id', explode(',', $spcffd00))->chunk(100, function ($sp704384) use($spd32bb5, $sp705266) { foreach ($sp704384 as $speb076b) { $speb076b->cards()->detach(); try { if ($spd32bb5) { $speb076b->fundRecord()->delete(); } if ($sp705266) { $spd05c92 = \App\User::lockForUpdate()->firstOrFail(); $spd05c92->m_all -= $speb076b->income; $spd05c92->saveOrFail(); } $speb076b->delete(); } catch (\Exception $spb62437) { } } }); return Response::success(); } function freeze(Request $sp0aae4c) { $this->validate($sp0aae4c, array('ids' => 'required|string')); $spcffd00 = explode(',', $sp0aae4c->post('ids')); $sp33a9d2 = $sp0aae4c->post('reason'); $sp3bfbd2 = 0; $spd37df0 = 0; foreach ($spcffd00 as $sp1948da) { $sp3bfbd2++; if (FundHelper::orderFreeze($sp1948da, $sp33a9d2)) { $spd37df0++; } } return Response::success(array($sp3bfbd2, $spd37df0)); } function unfreeze(Request $sp0aae4c) { $this->validate($sp0aae4c, array('ids' => 'required|string')); $spcffd00 = explode(',', $sp0aae4c->post('ids')); $sp3bfbd2 = 0; $spd37df0 = 0; $sp0b03be = \App\Order::STATUS_FROZEN; foreach ($spcffd00 as $sp1948da) { $sp3bfbd2++; if (FundHelper::orderUnfreeze($sp1948da, '后台操作', null, $sp0b03be)) { $spd37df0++; } } return Response::success(array($sp3bfbd2, $spd37df0, $sp0b03be)); } function set_paid(Request $sp0aae4c) { $this->validate($sp0aae4c, array('id' => 'required|integer')); $spaaa5c2 = $sp0aae4c->post('id', ''); $sp99e0fc = $sp0aae4c->post('trade_no', ''); if (strlen($sp99e0fc) < 1) { return Response::forbidden('请输入支付系统内单号'); } $speb076b = \App\Order::findOrFail($spaaa5c2); if ($speb076b->status !== \App\Order::STATUS_UNPAY) { return Response::forbidden('只能操作未支付订单'); } $spa8e328 = 'Admin.SetPaid'; $spdcb7fd = $speb076b->order_no; $sp7d07f3 = $speb076b->paid; try { Log::debug($spa8e328 . " shipOrder start, order_no: {$spdcb7fd}, amount: {$sp7d07f3}, trade_no: {$sp99e0fc}"); (new \App\Http\Controllers\Shop\Pay())->shipOrder($sp0aae4c, $spdcb7fd, $sp7d07f3, $sp99e0fc); Log::debug($spa8e328 . ' shipOrder end, order_no: ' . $spdcb7fd); $spd37df0 = true; $spa374f7 = '发货成功'; } catch (\Exception $spb62437) { $spd37df0 = false; $spa374f7 = $spb62437->getMessage(); Log::error($spa8e328 . ' shipOrder Exception: ' . $spb62437->getMessage()); } $speb076b = \App\Order::with(array('pay' => function (Relation $spb0a50e) { $spb0a50e->select(array('id', 'name')); }, 'card_orders.card' => function (Relation $spb0a50e) { $spb0a50e->select(array('id', 'card')); }))->findOrFail($spaaa5c2); if ($speb076b->status === \App\Order::STATUS_PAID) { if ($speb076b->product->delivery === \App\Product::DELIVERY_MANUAL) { $spd37df0 = true; $spa374f7 = '已标记为付款成功<br>当前商品为手动发货商品, 请手动进行发货。'; } else { $spd37df0 = false; $spa374f7 = '已标记为付款成功, <br>但是买家库存不足, 发货失败, 请稍后尝试手动发货。'; } } return Response::success(array('code' => $spd37df0 ? 0 : -1, 'msg' => $spa374f7, 'order' => $speb076b)); } }