<?php 

namespace App\Routing;

interface RouteInterface{

    public static function get(string $path ,array $controller, callable $callback);
   
}