<?php

namespace App\Routing;

class Route implements RouteInterface{

    public static string $path;
    public $callback;
    
    public function __construct($path, $callback)
    {
       $this->path = $path;
       $this->callback = $callback;
    }
    
    public static function get($path, ?array $controller, ?callable $callback){
        if($callback){
            call_user_func($callback);
        }else if ($controller){
            foreach ($controller as $controller_name => $controller_method){
                $controller_instance = new $controller_name();
                $controller_instance->$controller_method(); 
            }
        }
    }
   
}