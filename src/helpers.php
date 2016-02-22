<?php

if( !function_exists('router') ) {

    function router($name, $params = [])
    {
        return Localizator::route($name, $params);
    }

}

if( !function_exists('change_locale') ) {

    function change_locale($locale) {
        $route = Route::current();
        
        $params = array_merge($route->parameters(), ['lang' => $locale]);

        return router($route->getName(), $params);
    }

}