<?php

namespace App\Helpers; 

class Helpers{
    static function view($view = null, $data = []){
        include __DIR__.'/../../views/' . $view . ".view.php";
        return $data; 
    }

    static function loadEnv(){
        $envFile = __DIR__.'/../../.env';
        if(file_exists($envFile)){
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line){
                if(strpos($line, '=') !== false){
                    list($key, $value) = explode('=' , $line, 2 );
                    $key = trim($key);
                    $value = trim($value);
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }

    }
}

