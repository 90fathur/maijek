<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MerchantMenuModel;

class Menus extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $menuModel = new MerchantMenuModel();

        $menus = $menuModel->where('merchant_id', $payload['uid'])->findAll();

        foreach ($menus as &$menu) {
            if (!empty($menu['photo']) && strpos($menu['photo'], 'http') !== 0) {
                $menu['photo'] = base_url($menu['photo']);
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Daftar menu', 'data' => $menus]);
    }

    public function create()
    {
        $payload = $_SERVER['jwt_payload'];
        $rules = [
            'name' => 'required',
            'price' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal'], 400);
        }

        $discountPrice = $this->request->getVar('discount_price');
        $data = [
            'merchant_id' => $payload['uid'],
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
            'price' => $this->request->getVar('price'),
            'discount_price' => (!empty($discountPrice) && (float)$discountPrice > 0) ? $discountPrice : null,
            'is_available' => $this->request->getVar('is_available') ?? 1
        ];

        if ($this->request->getVar('category') !== null) {
            $data['category'] = $this->request->getVar('category');
        }
        if ($this->request->getVar('unit') !== null) {
            $data['unit'] = $this->request->getVar('unit');
        }

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/menus', $newName);
            $data['photo'] = 'uploads/menus/' . $newName;
        }

        $menuModel = new MerchantMenuModel();
        $menuModel->insert($data);

        return $this->respond(['status' => 201, 'message' => 'Menu berhasil ditambahkan'], 201);
    }

    public function update($id = null)
    {
        $id = $id ?? $this->request->getVar('id');
        $payload = $_SERVER['jwt_payload'];
        $menuModel = new MerchantMenuModel();

        $menu = $menuModel->find($id);
        if (!$menu || $menu['merchant_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak atau menu tidak ditemukan'], 403);
        }

        $rawPrice = $this->request->getVar('price');
        $price = ($rawPrice !== null && $rawPrice !== '') ? preg_replace('/[^0-9.]/', '', (string)$rawPrice) : $menu['price'];

        $discountPrice = $this->request->getVar('discount_price');
        if ($discountPrice !== null && $discountPrice !== '') {
            $discountPrice = preg_replace('/[^0-9.]/', '', (string)$discountPrice);
        }

        $data = [
            'name' => $this->request->getVar('name') ?? $menu['name'],
            'description' => $this->request->getVar('description') ?? $menu['description'],
            'price' => $price,
            'discount_price' => (!empty($discountPrice) && (float)$discountPrice > 0) ? $discountPrice : ($discountPrice === '0' || $discountPrice === '' ? null : ($menu['discount_price'] ?? null)),
            'is_available' => $this->request->getVar('is_available') ?? $menu['is_available']
        ];

        if ($this->request->getVar('category') !== null) {
            $data['category'] = $this->request->getVar('category');
        }
        if ($this->request->getVar('unit') !== null) {
            $data['unit'] = $this->request->getVar('unit');
        }

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/menus', $newName);
            $data['photo'] = 'uploads/menus/' . $newName;
        }

        try {
            $menuModel->update($id, $data);
            return $this->respond(['status' => 200, 'message' => 'Menu berhasil diubah']);
        } catch (\Exception $e) {
            return $this->respond(['status' => 500, 'message' => 'Gagal mengubah menu: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id = null)
    {
        $id = $id ?? $this->request->getVar('id');
        $payload = $_SERVER['jwt_payload'];
        $menuModel = new MerchantMenuModel();

        $menu = $menuModel->find($id);
        if (!$menu || $menu['merchant_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak atau menu tidak ditemukan'], 403);
        }

        try {
            $menuModel->delete($id);
            return $this->respond(['status' => 200, 'message' => 'Menu berhasil dihapus']);
        } catch (\Exception $e) {
            return $this->respond(['status' => 500, 'message' => 'Gagal menghapus menu: ' . $e->getMessage()], 500);
        }
    }

    public function addVariant()
    {
        $payload = $_SERVER['jwt_payload'];
        $menuId = $this->request->getVar('menu_id');

        // Check if menu belongs to merchant
        $menuModel = new \App\Models\MerchantMenuModel();
        $menu = $menuModel->find($menuId);
        if (!$menu || $menu['merchant_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak'], 403);
        }

        $variantModel = new \App\Models\MenuVariantModel();
        $variantId = $variantModel->insert([
            'menu_id' => $menuId,
            'name' => $this->request->getVar('name'),
            'type' => $this->request->getVar('type') ?? 'single', // single or multiple
            'is_required' => $this->request->getVar('is_required') ?? 0
        ]);

        return $this->respond(['status' => 201, 'message' => 'Variant berhasil ditambahkan', 'data' => ['variant_id' => $variantId]]);
    }

    public function removeVariant($variantId = null)
    {
        $variantModel = new \App\Models\MenuVariantModel();
        $variantModel->delete($variantId);
        return $this->respond(['status' => 200, 'message' => 'Variant dihapus']);
    }

    public function addVariantOption()
    {
        $variantId = $this->request->getVar('variant_id');
        $optionModel = new \App\Models\MenuVariantOptionModel();

        $optionId = $optionModel->insert([
            'variant_id' => $variantId,
            'name' => $this->request->getVar('name'),
            'extra_price' => $this->request->getVar('extra_price') ?? 0
        ]);

        return $this->respond(['status' => 201, 'message' => 'Opsi berhasil ditambahkan', 'data' => ['option_id' => $optionId]]);
    }

    public function removeVariantOption($optionId = null)
    {
        $optionModel = new \App\Models\MenuVariantOptionModel();
        $optionModel->delete($optionId);
        return $this->respond(['status' => 200, 'message' => 'Opsi dihapus']);
    }
}
