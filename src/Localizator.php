<?php

namespace Administr\Localization;

use Administr\Localization\Models\Language;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Session\Store as Session;
use Illuminate\Contracts\Routing\UrlGenerator;

class Localizator
{
    protected $app;
    protected $session;
    protected $url;

    public function __construct(Application $app, Session $session, UrlGenerator $url)
    {
        $this->app = $app;
        $this->session = $session;
        $this->url = $url;
    }

    /**
     * Set the current locale
     *
     * @param $locale
     */
    public function set($locale = null)
    {
        $locale = empty($locale) ? $this->app->getLocale() : $locale;

        $language = Language::where('code', $locale)->first(['id', 'code']);

        if(!$language) {
            return;
        }

        session(['lang' => $language->toArray()]);

        $this->app->setLocale($language->code);
    }

    /**
     * Get the current locale
     */
    public function get()
    {
        if( session()->has('lang') )
        {
            return session('lang.code');
        }

        return $this->app->getLocale();
    }

    public function hasBeenSet($locale = null)
    {
        $locale = empty($locale) ? $this->app->getLocale() : $locale;
        return session()->has('lang') && session('lang.code') == $locale;
    }

    public function route($name, $parameters = [], $absolute = true, $route = null)
    {
        return $this->url->route($name, array_merge([$this->get()], $parameters), $absolute, $route);
    }

}