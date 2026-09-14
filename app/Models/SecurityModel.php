<?php
namespace App\Models;
use App\Core\Database;

class SecurityModel extends Database {
    public function __construct() {
        $this->Security();
    }
}