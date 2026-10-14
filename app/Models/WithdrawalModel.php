<?php

namespace App\Models;

use CodeIgniter\Model;

class WithdrawalModel extends Model
{
    protected $table = 'withdrawals';
    protected $primaryKey = 'id';
    protected $allowedFields = ['driver_id', 'user_id', 'user_type', 'amount', 'bank_name', 'account_number', 'account_name', 'status'];
    protected $useTimestamps = true;
}
