<?php

namespace Administr\Localization;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Session\SessionManager as Session;
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
    public function set($locale)
    {
        $this->session->put([
            'locale'    => $locale
        ]);

        $this->app->setLocale($locale);
    }

    /**
     * Get the current locale
     */
    public function get()
    {
        if( $this->session->has('locale') )
        {
            return $this->session->get('locale');
        }

        return $this->app->getLocale();
    }

    public function route($name, $parameters = [], $absolute = true, $route = null)
    {
        return $this->url->route($name, array_merge([$this->get()], $parameters), $absolute, $route);
    }

}