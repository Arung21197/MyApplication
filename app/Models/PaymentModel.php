<?php
namespace App\Models;

use CodeIgniter\Model;
class PaymentModel extends Model {
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_id', 'payment_status', 'payment_response'];
}
