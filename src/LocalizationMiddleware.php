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
        $localizator  = app(Localizator::class);

        $locale = $request->segment(1, app()->getLocale());

        if( !$localizator->hasBeenSet($locale) )
        {
            $localizator->set($locale);
        }

        return $next($request);
    }
}