<?php

namespace Administr\Localization\Middleware;

use Administr\Localization\Models\Language;
use Closure;

class LocalizationMiddleware
{
    /**
     * @var Localizator
     */
    private $localizator;

    /**
     * @param Localizator $localizator
     */
    public function __construct(Localizator $localizator)
    {
        $this->localizator = $localizator;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1, app()->getLocale());

        $this->localizator->set($locale);

        return $next($request);
    }
}