<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuVariantModel extends Model
{
    protected $table = 'menu_variants';
    protected $primaryKey = 'id';
    protected $allowedFields = ['menu_id', 'name', 'type', 'is_required'];
    protected $useTimestamps = true;
}
