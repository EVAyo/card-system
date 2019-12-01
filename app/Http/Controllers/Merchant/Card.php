<?php
namespace App\Http\Controllers\Merchant; use App\Library\Response; use App\System; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Storage; class Card extends Controller { function get(Request $spa27895, $sp4de76b = false, $sp6b5a40 = false, $spd22a73 = false) { $sp7e6fa3 = $this->authQuery($spa27895, \App\Card::class)->with(array('product' => function ($sp7e6fa3) { $sp7e6fa3->select(array('id', 'name')); })); $spfe451d = $spa27895->input('search', false); $sp0edb8f = $spa27895->input('val', false); if ($spfe451d && $sp0edb8f) { if ($spfe451d == 'id') { $sp7e6fa3->where('id', $sp0edb8f); } else { $sp7e6fa3->where($spfe451d, 'like', '%' . $sp0edb8f . '%'); } } $sp980f44 = (int) $spa27895->input('category_id'); $sp112a12 = $spa27895->input('product_id', -1); if ($sp980f44 > 0) { if ($sp112a12 > 0) { $sp7e6fa3->where('product_id', $sp112a12); } else { $sp7e6fa3->whereHas('product', function ($sp7e6fa3) use($sp980f44) { $sp7e6fa3->where('category_id', $sp980f44); }); } } $spf66c3d = $spa27895->input('status'); if (strlen($spf66c3d)) { $sp7e6fa3->whereIn('status', explode(',', $spf66c3d)); } $sp117066 = (int) $spa27895->input('onlyCanSell'); if ($sp117066) { $sp7e6fa3->whereRaw('`count_all`>`count_sold`'); } $sp460df4 = $spa27895->input('type'); if (strlen($sp460df4)) { $sp7e6fa3->whereIn('type', explode(',', $sp460df4)); } $spe3e595 = $spa27895->input('trashed') === 'true'; if ($spe3e595) { $sp7e6fa3->onlyTrashed(); } if ($sp6b5a40 === true) { if ($spe3e595) { $sp7e6fa3->forceDelete(); } else { \App\Card::_trash($sp7e6fa3); } return Response::success(); } else { if ($spe3e595 && $spd22a73 === true) { \App\Card::_restore($sp7e6fa3); return Response::success(); } else { $sp7e6fa3->orderByRaw('`product_id`,`type`,`status`,`id`'); if ($sp4de76b === true) { $sp83cda3 = ''; $sp7e6fa3->chunk(100, function ($sp16ec83) use(&$sp83cda3) { foreach ($sp16ec83 as $sp089eb8) { $sp83cda3 .= $sp089eb8->card . '
'; } }); $sp69a6c1 = 'export_cards_' . $this->getUserIdOrFail($spa27895) . '_' . date('YmdHis') . '.txt'; $sp697dbb = array('Content-type' => 'text/plain', 'Content-Disposition' => sprintf('attachment; filename="%s"', $sp69a6c1), 'Content-Length' => strlen($sp83cda3)); return response()->make($sp83cda3, 200, $sp697dbb); } $spf69625 = $spa27895->input('current_page', 1); $spe8f5a8 = $spa27895->input('per_page', 20); $sp966ae1 = $sp7e6fa3->paginate($spe8f5a8, array('*'), 'page', $spf69625); return Response::success($sp966ae1); } } } function export(Request $spa27895) { return self::get($spa27895, true); } function trash(Request $spa27895) { $this->validate($spa27895, array('ids' => 'required|string')); $sp8e4c06 = $spa27895->post('ids'); $sp7e6fa3 = $this->authQuery($spa27895, \App\Card::class)->whereIn('id', explode(',', $sp8e4c06)); \App\Card::_trash($sp7e6fa3); return Response::success(); } function restoreTrashed(Request $spa27895) { $this->validate($spa27895, array('ids' => 'required|string')); $sp8e4c06 = $spa27895->post('ids'); $sp7e6fa3 = $this->authQuery($spa27895, \App\Card::class)->whereIn('id', explode(',', $sp8e4c06)); \App\Card::_restore($sp7e6fa3); return Response::success(); } function deleteTrashed(Request $spa27895) { $this->validate($spa27895, array('ids' => 'required|string')); $sp8e4c06 = $spa27895->post('ids'); $this->authQuery($spa27895, \App\Card::class)->whereIn('id', explode(',', $sp8e4c06))->forceDelete(); return Response::success(); } function deleteAll(Request $spa27895) { return $this->get($spa27895, false, true); } function restoreAll(Request $spa27895) { return $this->get($spa27895, false, false, true); } function add(Request $spa27895) { $sp112a12 = (int) $spa27895->post('product_id'); $sp16ec83 = $spa27895->post('card'); $sp460df4 = (int) $spa27895->post('type', \App\Card::TYPE_ONETIME); $sp46a9f0 = $spa27895->post('is_check') === 'true'; if (str_contains($sp16ec83, '<') || str_contains($sp16ec83, '>')) { return Response::fail('卡密不能包含 < 或 > 符号'); } $sp258cf6 = $this->getUserIdOrFail($spa27895); $sp40a694 = $this->authQuery($spa27895, \App\Product::class)->where('id', $sp112a12); $sp40a694->firstOrFail(array('id')); if ($sp460df4 === \App\Card::TYPE_REPEAT) { if ($sp46a9f0) { if (\App\Card::where('product_id', $sp112a12)->where('card', $sp16ec83)->exists()) { return Response::fail('该卡密已经存在，添加失败'); } } $sp089eb8 = new \App\Card(array('user_id' => $sp258cf6, 'product_id' => $sp112a12, 'card' => $sp16ec83, 'type' => \App\Card::TYPE_REPEAT, 'count_sold' => 0, 'count_all' => (int) $spa27895->post('count_all', 1))); if ($sp089eb8->count_all < 1 || $sp089eb8->count_all > 10000000) { return Response::forbidden('可售总次数不能超过10000000'); } return DB::transaction(function () use($sp40a694, $sp089eb8) { $sp089eb8->saveOrFail(); $sp1a3ec5 = $sp40a694->lockForUpdate()->firstOrFail(); $sp1a3ec5->count_all += $sp089eb8->count_all; $sp1a3ec5->saveOrFail(); return Response::success(); }); } else { $sp7c8ced = explode('
', $sp16ec83); $sp8a0be2 = count($sp7c8ced); $sp548e1c = 50000; if ($sp8a0be2 > $sp548e1c) { return Response::fail('每次添加不能超过 ' . $sp548e1c . ' 张'); } $sp3e85cb = array(); if ($sp46a9f0) { $sp5584cd = \App\Card::where('user_id', $sp258cf6)->where('product_id', $sp112a12)->get(array('card'))->all(); foreach ($sp5584cd as $sp06e543) { $sp3e85cb[] = $sp06e543['card']; } } $sp0000d8 = array(); $sp900843 = 0; for ($spe20094 = 0; $spe20094 < $sp8a0be2; $spe20094++) { $spa7f003 = trim($sp7c8ced[$spe20094]); if (strlen($spa7f003) < 1) { continue; } if (strlen($spa7f003) > 255) { return Response::fail('第 ' . $spe20094 . ' 张卡密 ' . $spa7f003 . ' 长度错误<br>卡密最大长度为255'); } if ($sp46a9f0) { if (in_array($spa7f003, $sp3e85cb)) { continue; } $sp3e85cb[] = $spa7f003; } $sp0000d8[] = array('user_id' => $sp258cf6, 'product_id' => $sp112a12, 'card' => $spa7f003, 'type' => \App\Card::TYPE_ONETIME); $sp900843++; } if ($sp900843 === 0) { return Response::success(); } return DB::transaction(function () use($sp40a694, $sp0000d8, $sp900843) { \App\Card::insert($sp0000d8); $sp1a3ec5 = $sp40a694->lockForUpdate()->firstOrFail(); $sp1a3ec5->count_all += $sp900843; $sp1a3ec5->saveOrFail(); return Response::success(); }); } } function edit(Request $spa27895) { $sp746ee1 = (int) $spa27895->post('id'); $sp089eb8 = $this->authQuery($spa27895, \App\Card::class)->findOrFail($sp746ee1); if ($sp089eb8) { $sp851f68 = $spa27895->post('card'); $sp460df4 = (int) $spa27895->post('type', \App\Card::TYPE_ONETIME); $sp31a840 = (int) $spa27895->post('count_all', 1); return DB::transaction(function () use($sp089eb8, $sp851f68, $sp460df4, $sp31a840) { $sp089eb8 = \App\Card::where('id', $sp089eb8->id)->lockForUpdate()->firstOrFail(); $sp089eb8->card = $sp851f68; $sp089eb8->type = $sp460df4; if ($sp089eb8->type === \App\Card::TYPE_REPEAT) { if ($sp31a840 < $sp089eb8->count_sold) { return Response::forbidden('可售总次数不能低于当前已售次数'); } if ($sp31a840 < 1 || $sp31a840 > 10000000) { return Response::forbidden('可售总次数不能超过10000000'); } $sp089eb8->count_all = $sp31a840; } else { $sp089eb8->count_all = 1; } $sp089eb8->saveOrFail(); $sp1a3ec5 = $sp089eb8->product()->lockForUpdate()->firstOrFail(); $sp1a3ec5->count_all -= $sp089eb8->count_all; $sp1a3ec5->count_all += $sp31a840; $sp1a3ec5->saveOrFail(); return Response::success(); }); } return Response::success(); } }