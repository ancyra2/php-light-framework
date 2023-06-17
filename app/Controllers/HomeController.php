<?php 

namespace App\Controllers;
use App\Routing\Route;
use App\Helpers\Helpers;
use App\Controllers\BaseControllerAbstract;
use App\Database\DatabaseEntity;
use App\Database\MysqlDatabase;
use App\Models\ModelInterface;

class HomeController extends BaseControllerAbstract{

    public string $testModel = "test";
    protected DatabaseEntity $db; 
    public function __construct()
    {
        Helpers::loadEnv();
        $db_connection = $_ENV['DB_CONNECTION'];

        if($db_connection == "mysql"){
            $this->db = MysqlDatabase::getInstance();
            $this->db->init();
        }
    } 
    public function index(){
        echo "index";
    }
    public function test(){
       $db = $this->db;
       $data = $db->getAll($this->testModel);
       print_r($data);
    }
}

