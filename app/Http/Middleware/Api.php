<?php

namespace App\Http\Middleware;

use Closure;
use Response;
class Api
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('post')&&(!isset($request->contact_email)|| empty($request->contact_email))) {
            # code...
            return Response::json([
                "success"=>false,
                "message"=>"contact_email not provided"
            ]);
        }
        return $next($request);
    }
}
