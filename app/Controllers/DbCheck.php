<?php

namespace App\Controllers;

class DbCheck extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        echo "<h1>Chat Dump</h1><pre>";
        $chats = $db->table('order_chats')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();
        print_r($chats);
        echo "</pre>";
    }
}
