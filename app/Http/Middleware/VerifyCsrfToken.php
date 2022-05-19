<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Closure;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/login/*', '/login', '/login/'
    ];
	
	private $gabliniAppExcept = ["login"];
	
	public function handle($request, Closure $next)
	{
		if($request->getHost() == "app.admin.gablini.hu") 
		{
			foreach($this->gabliniAppExcept AS $route)
			{
				if ($request->is($route)) { return $next($request); }
			}
		}
		
		return parent::handle($request, $next);
	}
}
