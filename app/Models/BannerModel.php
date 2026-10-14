<?php
namespace App\Models;
use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'image_path', 'status'];
    protected $useTimestamps = true;
}
