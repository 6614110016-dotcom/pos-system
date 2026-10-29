<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ตรวจสอบสิทธิ์การเข้าถึงเมนูตาม role ของผู้ใช้
 * ใช้งาน: ->middleware('role:admin,manager')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานเมนูนี้');
        }

        return $next($request);
    }
}
