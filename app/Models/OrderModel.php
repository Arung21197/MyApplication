<?php
namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends \CodeIgniter\Model {
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'quantity', 'total_price', 'status'];
}
