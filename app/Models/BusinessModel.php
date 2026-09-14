<?php
namespace App\Models;
use App\Core\Database;

class BusinessModel extends Database {
    public function __construct() {
        $this->Business();
    }
}