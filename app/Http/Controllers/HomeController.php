<?php
namespace App\Http\Controllers; use App\Category; use App\Library\Helper; use App\Pay; use App\Product; use App\System; use App\User; use App\UserDomain; use Illuminate\Database\Eloquent\Relations\Relation; use Illuminate\Http\Request; use App\Library\Geetest; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Cookie; class HomeController extends Controller { private function _shop_render($sp845283, $spd14ca3 = null, $sp1a3ec5 = null) { $spfd6e52 = array('url' => config('app.url'), 'company' => config('app.company'), 'name' => config('app.name'), 'logo' => config('app.logo'), 'functions' => array()); if (System::_getInt('product_manual')) { $spfd6e52['functions'][] = 'product_manual'; } if (System::_getInt('mail_send_order')) { $spfd6e52['functions'][] = 'mail_send_order'; if (System::_getInt('mail_send_order_use_contact')) { $spfd6e52['functions'][] = 'mail_send_order_use_contact'; } } if (System::_getInt('sms_send_order')) { $spfd6e52['functions'][] = 'sms_send_order'; $spfd6e52['sms_send_order'] = array('sms_price' => System::_getInt('sms_price')); } $spfd6e52['shop'] = array('name' => config('app.name'), 'qq' => System::_get('shop_qq'), 'ann' => System::_get('shop_ann'), 'ann_pop' => System::_get('shop_ann_pop'), 'inventory' => System::_getInt('shop_inventory')); if ($sp1a3ec5) { $spd14ca3->setVisible(array('id', 'name', 'password_open')); if ($spd14ca3->password_open) { $spd14ca3->setAttribute('password', $spd14ca3->getTmpPassword()); $spd14ca3->addVisible(array('password')); } $sp1a3ec5->setForShop($sp845283); $spfd6e52['categories'] = array($spd14ca3); $spfd6e52['product'] = $sp1a3ec5; $sp104c39 = $sp1a3ec5->name . ' - ' . $spfd6e52['name']; $sp64c022 = $sp1a3ec5->description; if (@$sp64c022[0] === '{') { $spb53f50 = array(); preg_match_all('/"insert":"(.+?)"/', $sp64c022, $spb53f50); $sp64c022 = str_replace('\\n', ' ', @join(' ', $spb53f50[1])); } } elseif ($spd14ca3) { $spd14ca3->setVisible(array('id', 'name', 'password_open')); $spfd6e52['categories'] = array($spd14ca3); $spfd6e52['product'] = null; $sp104c39 = $spd14ca3->name . ' - ' . $spfd6e52['name']; $sp64c022 = $spd14ca3->name; } else { $spc225f9 = Category::where('user_id', $sp845283->id)->orderBy('sort')->where('enabled', 1)->get(); foreach ($spc225f9 as $spd14ca3) { $spd14ca3->setVisible(array('id', 'name', 'password_open')); } $spfd6e52['categories'] = $spc225f9; $sp104c39 = $spfd6e52['name']; $sp64c022 = $spfd6e52['shop']['ann']; if (@$sp64c022[0] === '{') { $spb53f50 = array(); preg_match_all('/"insert":"(.+?)"/', $sp64c022, $spb53f50); $sp64c022 = str_replace('\\n', ' ', @join(' ', $spb53f50[1])); } } $spfd6e52['vcode'] = array('driver' => System::_get('vcode_driver'), 'buy' => (int) System::_get('vcode_shop_buy'), 'search' => (int) System::_get('vcode_shop_search')); if ($spfd6e52['vcode']['driver'] === 'geetest' && ($spfd6e52['vcode'] || $spfd6e52['search'])) { $spfd6e52['vcode']['geetest'] = Geetest\API::get(); } $spfd6e52['pays'] = Pay::whereRaw('enabled&' . (Helper::is_mobile() ? Pay::ENABLED_MOBILE : Pay::ENABLED_PC) . '!=0')->orderBy('sort')->get(array('id', 'name', 'img')); $spe44380 = Cookie::get('customer'); $sp620f5b = Cookie::make('customer', strlen($spe44380) !== 32 ? md5(str_random(16)) : $spe44380, 43200, null, null, false, false); $sp421ad6 = null; if (isset($_GET['theme'])) { $sp421ad6 = \App\ShopTheme::whereName($_GET['theme'])->first(); } if (!$sp421ad6) { $sp421ad6 = \App\ShopTheme::defaultTheme(); } $spfd6e52['theme'] = $sp845283->theme_config && isset($sp845283->theme_config[$sp421ad6->name]) ? $sp845283->theme_config[$sp421ad6->name] : $sp421ad6->config; if (isset($spfd6e52['theme']['background']) && $spfd6e52['theme']['background'] === '内置1') { $spfd6e52['theme']['background'] = Helper::b1_rand_background(); } if ($sp1a3ec5 === null) { if (@$spfd6e52['theme']['list_type'] === 'list') { foreach ($spfd6e52['categories'] as $spf10bec) { if (!$spf10bec->password_open) { $spf10bec->getProductsForShop(); } } } else { if (count($spfd6e52['categories']) === 1) { $spf10bec = $spfd6e52['categories'][0]; if (!$spf10bec->password_open) { $spf10bec->getProductsForShop(); } } } } return response()->view('shop_theme.' . $sp421ad6->name . '.index', array('name' => $sp104c39, 'title' => config('app.title'), 'keywords' => preg_replace('/[、，；。！？]/', ', ', $sp104c39), 'description' => $sp64c022, 'js_tj' => System::_get('js_tj'), 'js_kf' => System::_get('js_kf'), 'config' => $spfd6e52))->cookie($sp620f5b); } private function _shop_404() { $this->checkIsInMaintain(); return view('message', array('title' => '404 NotFound', 'message' => '该链接不存在<br>
<a style="font-size: 18px" href="/s/#/record">查询订单</a>')); } public function shop_default(Request $spa27895) { $sp818393 = $spa27895->get('tab', ''); return response()->redirectTo('/?theme=Material#/record?tab=' . $sp818393); } public function shop_category($sp3afab5) { $this->checkIsInMaintain(); $spd14ca3 = Category::whereId(Helper::id_decode($sp3afab5, Helper::ID_TYPE_CATEGORY))->with('user')->first(); if (!$spd14ca3 && is_numeric($spd14ca3)) { $spd14ca3 = Category::whereId($sp3afab5)->where('created_at', '<', \Carbon\Carbon::createFromDate(2019, 1, 1))->with('user')->first(); } if (!$spd14ca3) { return $this->_shop_404(); } return $this->_shop_render($spd14ca3->user, $spd14ca3); } public function shop_product(Request $spa27895, $sp2b52e9) { $this->checkIsInMaintain(); $sp1a3ec5 = Product::whereId(Helper::id_decode($sp2b52e9, Helper::ID_TYPE_PRODUCT))->with(array('user', 'category'))->first(); if (!$sp1a3ec5 && is_numeric($sp2b52e9)) { $sp1a3ec5 = Product::whereId($sp2b52e9)->where('created_at', '<', \Carbon\Carbon::createFromDate(2019, 1, 1))->with(array('user', 'category'))->first(); } if (!$sp1a3ec5 || !$sp1a3ec5->category) { return $this->_shop_404(); } if ($sp1a3ec5->password_open && $sp1a3ec5->password !== $spa27895->input('p')) { return view('message', array('title' => '当前商品需要密码', 'message' => ($spa27895->has('p') ? '密码错误，请重新输入' : '请输入密码') . '<br>
<div style="font-size: 14px">
<input id="password" type="password" style="display: block; margin: 8px 0 8px 0">
<button onclick="location.href=location.href.split(\'?\')[0]+\'?p=\'+encodeURI(document.getElementById(\'password\').value)">确认</button>
</div>
')); } return $this->_shop_render($sp1a3ec5->user, $sp1a3ec5->category, $sp1a3ec5); } public function shop() { $this->checkIsInMaintain(); $sp845283 = User::firstOrFail(); return $this->_shop_render($sp845283); } public function admin() { $spfd6e52 = array(); $spfd6e52['url'] = config('app.url'); if (System::_getInt('product_manual')) { $spfd6e52['functions'] = array('product_manual'); } $spfd6e52['vcode'] = array('driver' => System::_get('vcode_driver'), 'login' => (int) System::_get('vcode_login')); if ($spfd6e52['vcode']['driver'] === 'geetest') { $spfd6e52['vcode']['geetest'] = Geetest\API::get(); } return view('admin', array('config' => $spfd6e52)); } }