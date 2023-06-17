<?php

namespace App\Database;

interface DatabaseEntity {
    public static function getInstance() : DatabaseEntity;
    public function init();
    public function get($modelName, mixed $id): array;
    public function getAll($modelName): array;
    public function delete(string $table, mixed $id): bool;
    
}