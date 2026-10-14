<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use Firebase\JWT\JWT;

class Auth extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[3]',
            'phone'    => 'required|is_unique[users.phone]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $data = [
            'name'     => $this->request->getVar('name'),
            'phone'    => $this->request->getVar('phone'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT)
        ];

        $userModel->insert($data);

        return $this->respondCreated(['message' => 'User registered successfully']);
    }

    public function login()
    {
        $rules = [
            'phone'    => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->where('phone', $this->request->getVar('phone'))->first();

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        $verify = password_verify($this->request->getVar('password'), $user['password']);
        if (!$verify) {
            return $this->failUnauthorized('Invalid password');
        }

        $key = getenv('JWT_SECRET_KEY');
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour token
            'uid' => $user['id']
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond(['message' => 'Login successful', 'token' => $token, 'user' => ['id' => $user['id'], 'name' => $user['name']]]);
    }
}
