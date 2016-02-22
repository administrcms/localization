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

        $oldParams = $route->parameters();
        unset($oldParams['lang']);

        $params = array_merge([$locale], $oldParams);

        return route($route->getName(), $params);
    }

}