<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NotificationModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Notification extends ResourceController
{
    protected $format = 'json';

    private function getUserIdFromToken()
    {
        $header = $this->request->getHeaderLine('Authorization');
        $token = null;

        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) return null;

        try {
            $key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded->uid;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function index()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $notifModel = new NotificationModel();
        $notifications = $notifModel->getUserNotifications($userId);

        return $this->respond([
            'status' => 200,
            'message' => 'Success',
            'data' => $notifications
        ], 200);
    }

    public function markAsRead($id)
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $notifModel = new NotificationModel();
        $notif = $notifModel->find($id);

        if (!$notif || ($notif['user_id'] != $userId && $notif['user_id'] != null && $notif['user_id'] != 0)) {
            return $this->respond(['status' => 404, 'message' => 'Notification not found'], 404);
        }

        // Only mark as read if it's a specific user notification
        if ($notif['user_id'] == $userId) {
            $notifModel->update($id, ['is_read' => 1]);
        }

        return $this->respond(['status' => 200, 'message' => 'Marked as read', 'data' => null], 200);
    }
}
