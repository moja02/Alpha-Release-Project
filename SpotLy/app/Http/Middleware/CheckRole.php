<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // نتأكد أن المستخدم مسجل دخول وصلاحيته تطابق الدور المطلوب
        if (auth()->check() && auth()->user()->role === $role) {
            return $next($request);
        }

        // إذا لم يكن لديه صلاحية، نطرده لصفحة الدخول أو نعطيه 403
        return redirect('/login')->with('error', 'غير مصرح لك بالدخول!');
    }
}
