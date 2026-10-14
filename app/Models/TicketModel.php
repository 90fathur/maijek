<?php
namespace App\Models;
use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'driver_id', 'subject', 'status'];
    protected $useTimestamps = true;
}
