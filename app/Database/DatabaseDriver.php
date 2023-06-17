<?php

namespace App\Database;

class DatabaseDriver {
    protected DatabaseEntity $db;

    public function __construct(DatabaseEntity $db)
    {
        $this->db = $db;
    }

   
} 