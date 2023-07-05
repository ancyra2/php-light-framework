<?php

namespace App\Database;

use App\Models\ModelInterface;
use PDO;
use PDOException;
use App\Patterns\Singleton;
use App\Helpers\Helpers;

class MysqlDatabase implements DatabaseEntity{

    protected PDO $pdo;
    
    private static $instance;

    public static function getInstance() : MysqlDatabase{
        $class = static::class;
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function init(){
        Helpers::loadEnv();
        $db_host = $_ENV['DB_HOST'];
        $db_user = $_ENV['DB_USERNAME'];
        $db_password = $_ENV['DB_PASSWORD'];
        $db_name = $_ENV['DB_DATABASE'];
        $dsn = 'mysql:dbname=' . $db_name . ';host=' . $db_host; 
        try{
            $this->pdo = new PDO($dsn, $db_user, $db_password);
        }catch(PDOException $e){
            echo $e->getMessage();
        } 
    }

    public function get($modelName, $id): array{
        $stmt = $this->pdo->prepare("SELECT * FROM $modelName WHERE id = $id");
        $stmt-> execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function getAll($modelName): array{
        $stmt = $this->pdo->prepare("SELECT * FROM $modelName");
        $stmt-> execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function delete($modelName, mixed $id): bool{
        return $this->pdo->prepare("DELETE FROM $modelName WHERE id=$id")->execute();
    }
    
}