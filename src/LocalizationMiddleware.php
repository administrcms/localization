<?php

namespace Administr\Localization;

use Administr\Localization\Models\Language;
use Closure;

class LocalizationMiddleware
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
        if( !session()->has('lang') )
        {
            $language = Language::where('code', app()->getLocale())->first(['id', 'code']);
            session(['lang' => $language->toArray()]);
        } else {
            app()->setLocale(session('lang.code'));
        }

        return $next($request);
    }
}