<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuVariantOptionModel extends Model
{
    protected $table = 'menu_variant_options';
    protected $primaryKey = 'id';
    protected $allowedFields = ['variant_id', 'name', 'extra_price'];
    protected $useTimestamps = true;
}
