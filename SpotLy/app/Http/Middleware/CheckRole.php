<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // نتأكد أن المستخدم مسجل دخول وصلاحيته تطابق أحد الأدوار المطلوبة
        if (auth()->check() && in_array(auth()->user()->role, $roles)) {
            return $next($request);
        }

        // إذا كان الطلب يتوقع استجابة JSON أو يبدأ بـ api، نرجع 403 بدلاً من التوجيه
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to access this resource.'
            ], 403);
        }

        // إذا لم يكن لديه صلاحية، نطرده لصفحة الدخول أو نعطيه 403
        return redirect('/login')->with('error', 'غير مصرح لك بالدخول!');
    }
}
