<?php

if( !function_exists('router') ) {

    function router($name, $params = [])
    {
        return Locale::route($name, $params);
    }

}