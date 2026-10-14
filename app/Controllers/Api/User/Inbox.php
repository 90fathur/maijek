<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Inbox extends ResourceController
{
    protected $format = 'json';
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

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

    public function chats()
    {
        $userId = $this->getUserIdFromToken();
        if (!$userId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $inboxList = [];

        // 1. Get latest CS Chat
        $latestCsChat = $this->db->table('cs_chats')
            ->where('room_id', 'user_' . $userId)
            ->orderBy('created_at', 'DESC')
            ->get()->getRow();

        // 2. Count unread CS Chats
        $unreadCsChatCount = 0;
        try {
            if ($this->db->fieldExists('is_read', 'cs_chats')) {
                $unreadCsChatCount = $this->db->table('cs_chats')
                    ->where('room_id', 'user_' . $userId)
                    ->where('sender_type', 'admin') // From CS to user
                    ->where('is_read', 0)
                    ->countAllResults();
            }
        } catch (\Exception $e) {
        }

        if ($latestCsChat) {
            $inboxList[] = [
                'id' => 'cs',
                'name' => 'Maijek Support',
                'last_message' => $latestCsChat->message,
                'time' => $latestCsChat->created_at,
                'initials' => 'MS',
                'is_unread' => $unreadCsChatCount > 0,
                'type' => 'cs',
                'target_id' => 'user_' . $userId,
            ];
        }

        // 3. Get latest Order Chat (if order is active)
        $activeOrders = $this->db->table('orders')
            ->where('user_id', $userId)
            ->whereIn('status', ['accepted', 'in_progress', 'picking_up', 'driver_arrived'])
            ->get()->getResult();

        foreach ($activeOrders as $order) {
            $latestOrderChat = $this->db->table('order_chats')
                ->where('order_id', $order->id)
                ->orderBy('created_at', 'DESC')
                ->get()->getRow();

            if ($latestOrderChat) {
                $driver = $this->db->table('drivers')->where('id', $order->driver_id)->get()->getRow();
                $driverName = $driver ? $driver->name : 'Pengemudi';
                $initials = strtoupper(substr($driverName, 0, 2));

                $unreadCount = 0;
                try {
                    if ($this->db->fieldExists('is_read', 'order_chats')) {
                        $unreadCount = $this->db->table('order_chats')
                            ->where('order_id', $order->id)
                            ->where('sender_type', 'driver')
                            ->where('is_read', 0)
                            ->countAllResults();
                    }
                } catch (\Exception $e) {
                }

                $inboxList[] = [
                    'id' => 'order_' . $order->id,
                    'name' => $driverName . ' (Pesanan #' . $order->id . ')',
                    'last_message' => $latestOrderChat->message,
                    'time' => $latestOrderChat->created_at,
                    'initials' => $initials,
                    'is_unread' => $unreadCount > 0,
                    'type' => 'ride',
                    'target_id' => $order->id,
                ];
            }
        }

        // Sort by time DESC
        usort($inboxList, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return $this->respond([
            'status' => 200,
            'message' => 'Success',
            'data' => $inboxList
        ]);
    }
}
