<?php 

namespace App\Models;

class TestModel implements ModelInterface{
    public static int $id;
    public static string $model_name = "test";
    public static string $test_name;
    public static int $test_level;
}