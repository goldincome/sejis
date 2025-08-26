<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
            
        if (!$user || !in_array($user->user_type, [UserTypeEnum::ADMIN->value, UserTypeEnum::SUPER_ADMIN->value])) {
             abort(403, 'Unauthorized action.');
            //return redirect()->route('admin.login'); //abort(403);
        }

        return $next($request);
    }
}
