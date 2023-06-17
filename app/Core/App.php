<?php

namespace App\Core;

abstract class App implements CoreInterface{

    private object $middlewareObject;
    public function construct(){

    }

    public function use(object $middlewareObject): object{
        $middlewareObject = $this->middlewareObject;
        return $middlewareObject;
    }
}