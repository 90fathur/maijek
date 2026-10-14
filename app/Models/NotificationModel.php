<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = ['user_id', 'title', 'message', 'target', 'type', 'is_read'];

    public function getUserNotifications($userId)
    {
        // Pastikan userId difilter dengan aman
        $userId = (int) $userId;

        return $this->where("(user_id = $userId) OR ((user_id IS NULL OR user_id = 0) AND target IN ('all', 'users'))")
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
