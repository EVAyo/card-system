<?php
namespace App\Http\Middleware; use Closure; use Illuminate\Support\Facades\Auth; class RedirectIfAuthenticated { public function handle($sp16eb02, Closure $sp8e98e0, $spaddca9 = null) { if (Auth::guard($spaddca9)->check()) { return redirect('/home'); } return $sp8e98e0($sp16eb02); } }