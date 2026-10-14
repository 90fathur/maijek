<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MerchantModel;
use App\Models\MerchantMenuModel;

class Merchants extends BaseController
{
    public function index()
    {
        $merchantModel = new MerchantModel();
        $q = trim($this->request->getGet('q') ?? '');
        $merchantType = trim($this->request->getGet('merchant_type') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $merchantModel->select('merchants.*, (SELECT COUNT(*) FROM merchant_menus WHERE merchant_menus.merchant_id = merchants.id) as total_menus');

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('name', $q)
                ->orLike('phone', $q)
                ->orLike('address', $q)
                ->orLike('category', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($merchantType !== '' && in_array($merchantType, ['food', 'mart'])) {
            $builder = $builder->where('merchant_type', $merchantType);
        }

        if ($status !== '' && in_array($status, ['approved', 'pending', 'rejected', 'unverified'])) {
            $builder = $builder->where('verification_status', $status);
        }

        $merchants = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $merchantModel->pager;

        $data = [
            'title'         => 'Manajemen Mitra UMKM | Maijek Admin',
            'merchants'     => $merchants,
            'pager'         => $pager,
            'perPage'       => $perPage,
            'q'             => $q,
            'merchant_type' => $merchantType,
            'status'        => $status,
            'totalCount'    => $pager->getTotal(),
        ];

        return view('admin/merchants/index', $data);
    }

    public function store()
    {
        $name = $this->request->getPost('name');
        $phone = $this->request->getPost('phone');
        $password = $this->request->getPost('password');
        $address = $this->request->getPost('address');

        if (!empty($name) && !empty($phone) && !empty($password) && !empty($address)) {
            $merchantModel = new MerchantModel();

            // Check for duplicate phone number
            $existing = $merchantModel->where('phone', $phone)->first();
            if ($existing) {
                session()->setFlashdata('success', 'Gagal: Nomor HP sudah digunakan!');
                return redirect()->to('/admin/merchants');
            }

            $photoPath = null;
            $file = $this->request->getFile('photo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if (!is_dir(FCPATH . 'uploads/merchants')) {
                    mkdir(FCPATH . 'uploads/merchants', 0777, true);
                }
                $ext = $file->guessExtension() ?? $file->getExtension();
                if (empty($ext) || $ext === 'bin' || $ext === '') $ext = 'jpg';
                $newName = uniqid('merchant_') . '.' . $ext;
                $file->move(FCPATH . 'uploads/merchants', $newName);
                $photoPath = 'uploads/merchants/' . $newName;
            }

            $merchantType = $this->request->getPost('merchant_type') ?: 'food';
            $category = $this->request->getPost('category') ?: '';

            $merchantModel->insert([
                'name' => $name,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'address' => $address,
                'photo' => $photoPath,
                'merchant_type' => $merchantType,
                'category' => $category,
                'latitude' => 0, // Default for manual addition
                'longitude' => 0,
                'is_open' => 1,
                'verification_status' => 'approved', // Directly approved
                'balance' => 0
            ]);

            session()->setFlashdata('success', 'Mitra UMKM (' . ($merchantType === 'mart' ? 'MaiMart' : 'MaiFood') . ') berhasil ditambahkan dan langsung diverifikasi!');
        }

        return redirect()->to('/admin/merchants');
    }

    public function updatePhoto($id)
    {
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/merchants')) {
                mkdir(FCPATH . 'uploads/merchants', 0777, true);
            }
            $ext = $file->guessExtension() ?? $file->getExtension();
            if (empty($ext) || $ext === 'bin' || $ext === '') $ext = 'jpg';
            $newName = uniqid('merchant_') . '.' . $ext;
            $file->move(FCPATH . 'uploads/merchants', $newName);

            $merchantModel = new MerchantModel();
            $merchantModel->update($id, ['photo' => 'uploads/merchants/' . $newName]);
            session()->setFlashdata('success', 'Foto profil mitra UMKM berhasil diperbarui!');
        } else {
            session()->setFlashdata('error', 'Gagal mengunggah foto!');
        }

        return redirect()->to('/admin/merchants');
    }

    public function changeStatus($id)
    {
        $status = $this->request->getPost('status');

        // This allows admin to suspend or re-approve a merchant
        if (in_array($status, ['approved', 'suspended', 'rejected'])) {
            $merchantModel = new MerchantModel();
            $updateData = ['verification_status' => $status];

            if ($status !== 'approved') {
                $updateData['is_open'] = 0; // Force close if suspended/rejected
            }

            $merchantModel->update($id, $updateData);
            session()->setFlashdata('success', 'Status mitra UMKM berhasil diubah.');
        }

        return redirect()->to('/admin/merchants');
    }

    public function resetPassword($id)
    {
        $newPassword = $this->request->getPost('new_password');

        if (!empty($newPassword)) {
            $merchantModel = new MerchantModel();
            $merchantModel->update($id, [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
            session()->setFlashdata('success', 'Password mitra UMKM berhasil di-reset.');
        }

        return redirect()->to('/admin/merchants');
    }

    public function update($id)
    {
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($id);
        if (!$merchant) {
            session()->setFlashdata('error', 'Mitra UMKM tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        $name = trim($this->request->getPost('name') ?? '');
        $phone = trim($this->request->getPost('phone') ?? '');
        $merchantType = trim($this->request->getPost('merchant_type') ?? '');
        $category = trim($this->request->getPost('category') ?? '');
        $address = trim($this->request->getPost('address') ?? '');
        $isOpen = $this->request->getPost('is_open');
        $verificationStatus = trim($this->request->getPost('verification_status') ?? '');

        if ($name === '') {
            session()->setFlashdata('error', 'Nama toko/usaha tidak boleh kosong.');
            return redirect()->to('/admin/merchants');
        }

        if ($phone !== '' && $phone !== $merchant['phone']) {
            $existing = $merchantModel->where('phone', $phone)->where('id !=', $id)->first();
            if ($existing) {
                session()->setFlashdata('error', 'Gagal: Nomor HP ' . esc($phone) . ' sudah digunakan oleh mitra lain!');
                return redirect()->to('/admin/merchants');
            }
        }

        $updateData = [
            'name'     => $name,
            'category' => $category,
        ];

        if ($phone !== '') {
            $updateData['phone'] = $phone;
        }

        if (in_array($merchantType, ['food', 'mart', 'all'])) {
            $updateData['merchant_type'] = $merchantType;
        }

        if ($address !== '') {
            $updateData['address'] = $address;
        }

        if ($isOpen !== null && $isOpen !== '') {
            $updateData['is_open'] = (int)$isOpen;
        }

        if (in_array($verificationStatus, ['approved', 'pending', 'rejected', 'suspended'])) {
            $updateData['verification_status'] = $verificationStatus;
        }

        $merchantModel->update($id, $updateData);

        session()->setFlashdata('success', 'Data mitra UMKM "' . esc($name) . '" (Kategori: ' . esc($category ?: 'Umum') . ', Layanan: ' . ($merchantType === 'mart' ? 'MaiMart' : 'MaiFood') . ') berhasil diperbarui!');
        return redirect()->to('/admin/merchants');
    }

    public function delete($id)
    {
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            session()->setFlashdata('error', 'Mitra UMKM tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        $menuModel = new MerchantMenuModel();

        // 1. Bersihkan file foto menu-menu merchant jika ada
        $menus = $menuModel->where('merchant_id', $id)->findAll();
        $menuCount = count($menus);
        foreach ($menus as $menu) {
            if (!empty($menu['photo']) && file_exists(FCPATH . $menu['photo'])) {
                @unlink(FCPATH . $menu['photo']);
            }
        }

        // 2. Bersihkan file foto merchant jika ada
        if (!empty($merchant['photo']) && file_exists(FCPATH . $merchant['photo'])) {
            @unlink(FCPATH . $merchant['photo']);
        }
        if (!empty($merchant['ktp_image']) && file_exists(FCPATH . $merchant['ktp_image'])) {
            @unlink(FCPATH . $merchant['ktp_image']);
        }
        if (!empty($merchant['business_image']) && file_exists(FCPATH . $merchant['business_image'])) {
            @unlink(FCPATH . $merchant['business_image']);
        }

        // 3. Hapus menu dan merchant dari database
        $menuModel->where('merchant_id', $id)->delete();
        $merchantModel->delete($id);

        session()->setFlashdata('success', 'Mitra UMKM "' . esc($merchant['name']) . '" beserta ' . $menuCount . ' menu berhasil dihapus secara permanen.');
        return redirect()->to('/admin/merchants');
    }

    public function menus($id)
    {
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($id);

        if (!$merchant) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Mitra UMKM tidak ditemukan']);
        }

        $menuModel = new MerchantMenuModel();
        $menus = $menuModel->where('merchant_id', $id)->orderBy('id', 'DESC')->findAll();

        return $this->response->setJSON([
            'status'   => 200,
            'merchant' => [
                'id'            => $merchant['id'],
                'name'          => $merchant['name'],
                'merchant_type' => $merchant['merchant_type'] ?? 'food',
                'category'      => $merchant['category'] ?? 'Umum',
            ],
            'total'    => count($menus),
            'menus'    => $menus
        ]);
    }

    public function deleteMenu($menuId)
    {
        $menuModel = new MerchantMenuModel();
        $menu = $menuModel->find($menuId);

        if (!$menu) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['status' => 404, 'message' => 'Menu tidak ditemukan.']);
            }
            session()->setFlashdata('error', 'Menu tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        if (!empty($menu['photo']) && file_exists(FCPATH . $menu['photo'])) {
            @unlink(FCPATH . $menu['photo']);
        }

        $menuName = $menu['name'];
        $menuModel->delete($menuId);

        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'status'  => 200,
                'message' => 'Menu "' . $menuName . '" berhasil dihapus.'
            ]);
        }

        session()->setFlashdata('success', 'Menu "' . esc($menuName) . '" berhasil dihapus.');
        return redirect()->to('/admin/merchants');
    }

    public function updateMenu($menuId)
    {
        $menuModel = new MerchantMenuModel();
        $menu = $menuModel->find($menuId);

        if (!$menu) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['status' => 404, 'message' => 'Menu tidak ditemukan.']);
            }
            session()->setFlashdata('error', 'Menu tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        $name = trim($this->request->getPost('name') ?? '');
        $category = trim($this->request->getPost('category') ?? '');
        $unit = trim($this->request->getPost('unit') ?? 'pcs');
        $price = (float)($this->request->getPost('price') ?? 0);
        $discountPrice = $this->request->getPost('discount_price');
        $description = trim($this->request->getPost('description') ?? '');
        $isAvailable = $this->request->getPost('is_available');

        if ($name === '') {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['status' => 400, 'message' => 'Nama menu tidak boleh kosong.']);
            }
            session()->setFlashdata('error', 'Nama menu tidak boleh kosong.');
            return redirect()->to('/admin/merchants');
        }

        $updateData = [
            'name'         => $name,
            'category'     => $category,
            'unit'         => $unit ?: 'pcs',
            'price'        => $price,
            'description'  => $description,
            'is_available' => ($isAvailable !== null && $isAvailable !== '') ? (int)$isAvailable : 1,
        ];

        if ($discountPrice !== null && $discountPrice !== '' && (float)$discountPrice > 0) {
            $discVal = (float)$discountPrice;
            $updateData['discount_price'] = ($discVal < $price) ? $discVal : null;
        } else {
            $updateData['discount_price'] = null;
        }

        // Handle upload foto menu jika ada file baru
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/menus')) {
                @mkdir(FCPATH . 'uploads/menus', 0777, true);
            }
            $newName = $photoFile->getRandomName();
            $photoFile->move(FCPATH . 'uploads/menus', $newName);

            // Hapus foto lama jika ada
            if (!empty($menu['photo']) && file_exists(FCPATH . $menu['photo'])) {
                @unlink(FCPATH . $menu['photo']);
            }

            $updateData['photo'] = 'uploads/menus/' . $newName;
        }

        $menuModel->update($menuId, $updateData);
        $updatedMenu = $menuModel->find($menuId);

        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'status'  => 200,
                'message' => 'Menu "' . $name . '" berhasil diperbarui!',
                'menu'    => $updatedMenu
            ]);
        }

        session()->setFlashdata('success', 'Menu "' . esc($name) . '" berhasil diperbarui.');
        return redirect()->to('/admin/merchants');
    }

    public function addMenu($merchantId)
    {
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['status' => 404, 'message' => 'Mitra UMKM tidak ditemukan.']);
            }
            session()->setFlashdata('error', 'Mitra UMKM tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        $name = trim($this->request->getPost('name') ?? '');
        $category = trim($this->request->getPost('category') ?? '');
        $unit = trim($this->request->getPost('unit') ?? (($merchant['merchant_type'] ?? '') === 'mart' ? 'pcs' : 'porsi'));
        $price = (float)($this->request->getPost('price') ?? 0);
        $discountPrice = $this->request->getPost('discount_price');
        $description = trim($this->request->getPost('description') ?? '');
        $isAvailable = (int)($this->request->getPost('is_available') ?? 1);

        if ($name === '') {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON(['status' => 400, 'message' => 'Nama menu tidak boleh kosong.']);
            }
            session()->setFlashdata('error', 'Nama menu tidak boleh kosong.');
            return redirect()->to('/admin/merchants');
        }

        $insertData = [
            'merchant_id'    => (int)$merchantId,
            'name'           => $name,
            'category'       => $category,
            'unit'           => $unit ?: 'pcs',
            'price'          => $price,
            'description'    => $description,
            'is_available'   => $isAvailable,
            'discount_price' => null
        ];

        if ($discountPrice !== null && $discountPrice !== '' && (float)$discountPrice > 0) {
            $discVal = (float)$discountPrice;
            if ($discVal < $price) {
                $insertData['discount_price'] = $discVal;
            }
        }

        // Upload foto
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/menus')) {
                @mkdir(FCPATH . 'uploads/menus', 0777, true);
            }
            $newName = $photoFile->getRandomName();
            $photoFile->move(FCPATH . 'uploads/menus', $newName);
            $insertData['photo'] = 'uploads/menus/' . $newName;
        }

        $menuModel = new MerchantMenuModel();
        $newId = $menuModel->insert($insertData);

        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'status'  => 200,
                'message' => 'Menu "' . $name . '" berhasil ditambahkan!',
                'menu_id' => $newId
            ]);
        }

        session()->setFlashdata('success', 'Menu "' . esc($name) . '" berhasil ditambahkan.');
        return redirect()->to('/admin/merchants');
    }

    public function deleteAllMenus($merchantId)
    {
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            session()->setFlashdata('error', 'Mitra UMKM tidak ditemukan.');
            return redirect()->to('/admin/merchants');
        }

        $menuModel = new MerchantMenuModel();
        $menus = $menuModel->where('merchant_id', $merchantId)->findAll();
        $count = count($menus);

        foreach ($menus as $m) {
            if (!empty($m['photo']) && file_exists(FCPATH . $m['photo'])) {
                @unlink(FCPATH . $m['photo']);
            }
        }

        $menuModel->where('merchant_id', $merchantId)->delete();

        session()->setFlashdata('success', 'Seluruh menu (' . $count . ' menu) milik mitra "' . esc($merchant['name']) . '" berhasil dihapus.');
        return redirect()->to('/admin/merchants');
    }
}


