<?php


use App\Helpers\Helpers;
use App\Routing\Route; 
use App\Controllers\HomeController;

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        switch ($uri){
            case '/':
                Route::get('/',null,function(){
                    Helpers::view("test");
                });
            break;
            case '/test':
                Route::get('/test',[HomeController::class => 'test'],null);
            break;
            default:
            echo "Böyle bir sayfa yok";
        }
        break;        
    case 'POST':
        break; 
    case 'PUT':
        break; 
    case 'DELETE':
        break;                 
    
        
}

//echo App\Controllers\HomeController::class;

//Route::get('/home',App\Controllers\HomeController::class);