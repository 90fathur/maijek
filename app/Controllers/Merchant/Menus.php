<?php

namespace App\Controllers\Merchant;

use App\Controllers\BaseController;
use App\Models\MerchantMenuModel;
use App\Models\MerchantModel;

class Menus extends BaseController
{
    public function index()
    {
        $merchantId = session()->get('merchant_id');
        $menuModel = new MerchantMenuModel();
        $menus = $menuModel->where('merchant_id', $merchantId)->orderBy('created_at', 'DESC')->findAll();

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        $isMart = ($merchant && ($merchant['merchant_type'] ?? '') === 'mart');
        if (!session()->get('merchant_type') && $merchant) {
            session()->set('merchant_type', $merchant['merchant_type'] ?? 'food');
        }

        return view('merchant/menus/index', [
            'title'    => $isMart ? 'Kelola Produk Toko | MaiMart Panel' : 'Kelola Menu Makanan | MaiFood Panel',
            'merchant' => $merchant,
            'isMart'   => $isMart,
            'menus'    => $menus
        ]);
    }

    public function store()
    {
        $merchantId = session()->get('merchant_id');
        $name = trim($this->request->getPost('name') ?? '');
        $price = $this->request->getPost('price');
        $discountPrice = $this->request->getPost('discount_price');
        $description = trim($this->request->getPost('description') ?? '');
        $category = trim($this->request->getPost('category') ?? '');
        $unit = trim($this->request->getPost('unit') ?? '');

        if ($unit === 'custom') {
            $unit = trim($this->request->getPost('custom_unit') ?? '');
        }

        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        $isMart = (($merchant['merchant_type'] ?? '') === 'mart');

        if (empty($unit)) {
            $unit = $isMart ? 'pcs' : 'porsi';
        }
        if (empty($category)) {
            $category = $isMart ? 'Sembako' : 'Makanan';
        }

        if (empty($name) || empty($price)) {
            return redirect()->back()->with('error', 'Nama ' . ($isMart ? 'produk' : 'menu') . ' dan harga wajib diisi.');
        }

        $photoPath = null;
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/menus')) {
                mkdir(FCPATH . 'uploads/menus', 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/menus', $newName);
            $photoPath = 'uploads/menus/' . $newName;
        }

        $menuModel = new MerchantMenuModel();
        $menuModel->insert([
            'merchant_id'    => $merchantId,
            'name'           => $name,
            'category'       => $category,
            'unit'           => $unit,
            'price'          => $price,
            'discount_price' => (!empty($discountPrice) && (float)$discountPrice > 0) ? $discountPrice : null,
            'description'    => $description,
            'photo'          => $photoPath,
            'is_available'   => 1
        ]);

        return redirect()->to('/merchant/menus')->with('success', ($isMart ? 'Produk' : 'Menu') . ' baru berhasil ditambahkan.');
    }

    public function update($id)
    {
        $merchantId = session()->get('merchant_id');
        $menuModel = new MerchantMenuModel();
        $menu = $menuModel->where('id', $id)->where('merchant_id', $merchantId)->first();

        if (!$menu) {
            return redirect()->back()->with('error', 'Item tidak ditemukan.');
        }

        $merchantModel = new \App\Models\MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        $isMart = (($merchant['merchant_type'] ?? '') === 'mart');

        $name = trim($this->request->getPost('name') ?? '');
        $price = $this->request->getPost('price');
        $discountPrice = $this->request->getPost('discount_price');
        $description = trim($this->request->getPost('description') ?? '');
        $category = trim($this->request->getPost('category') ?? '');
        $unit = trim($this->request->getPost('unit') ?? '');

        if ($unit === 'custom') {
            $unit = trim($this->request->getPost('custom_unit') ?? '');
        }

        if (empty($unit)) {
            $unit = $menu['unit'] ?? ($isMart ? 'pcs' : 'porsi');
        }
        if (empty($category)) {
            $category = $menu['category'] ?? ($isMart ? 'Sembako' : 'Makanan');
        }

        $data = [
            'name'           => $name,
            'price'          => $price,
            'discount_price' => (!empty($discountPrice) && (float)$discountPrice > 0) ? $discountPrice : null,
            'description'    => $description,
            'category'       => $category,
            'unit'           => $unit
        ];

        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/menus')) {
                mkdir(FCPATH . 'uploads/menus', 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/menus', $newName);
            $data['photo'] = 'uploads/menus/' . $newName;
        }

        $menuModel->update($id, $data);

        return redirect()->to('/merchant/menus')->with('success', ($isMart ? 'Produk' : 'Menu') . ' berhasil diperbarui.');
    }

    public function toggleAvailability($id)
    {
        $merchantId = session()->get('merchant_id');
        $menuModel = new MerchantMenuModel();
        $menu = $menuModel->where('id', $id)->where('merchant_id', $merchantId)->first();

        if (!$menu) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Menu tidak ditemukan']);
        }

        $newStatus = $menu['is_available'] == 1 ? 0 : 1;
        $menuModel->update($id, ['is_available' => $newStatus]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'       => 200,
                'is_available' => $newStatus,
                'message'      => $newStatus == 1 ? 'Menu sekarang TERSEDIA' : 'Menu ditandai HABIS'
            ]);
        }

        return redirect()->back()->with('success', 'Status ketersediaan menu diperbarui.');
    }

    public function delete($id)
    {
        $merchantId = session()->get('merchant_id');
        $menuModel = new MerchantMenuModel();
        $menu = $menuModel->where('id', $id)->where('merchant_id', $merchantId)->first();

        if ($menu) {
            $menuModel->delete($id);
            return redirect()->to('/merchant/menus')->with('success', 'Menu berhasil dihapus.');
        }

        return redirect()->to('/merchant/menus')->with('error', 'Menu tidak ditemukan.');
    }
}
