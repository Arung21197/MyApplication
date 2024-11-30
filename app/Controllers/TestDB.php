<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;

class TestDB extends Controller
{
    public function index()
    {
        try {
            // Load the database
            $db = \Config\Database::connect();

            // Check the connection
            if ($db->connect()) {
                return "Database connection successful!";
            }
        } catch (DatabaseException $e) {
            return "Database connection failed: " . $e->getMessage();
        }
    }
}
