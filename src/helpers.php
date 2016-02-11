<?php

if( !function_exists('router') ) {

    function router($name, $params = [])
    {
        return Localizator::route($name, $params);
    }

}