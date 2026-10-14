<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\PromoModel;

class Promos extends BaseController
{
    public function index()
    {
        $promoModel = new PromoModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $promoModel;

        if ($q !== '') {
            $builder = $builder->like('code', $q);
        }

        if ($status !== '' && in_array($status, ['active', 'inactive'])) {
            $builder = $builder->where('status', $status);
        }

        $promos = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $promoModel->pager;

        $data = [
            'title'      => 'Manajemen Promo | Maijek Admin',
            'promos'     => $promos,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'status'     => $status,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/promos/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Buat Promo Baru | Maijek Admin'
        ];
        return view('admin/promos/create', $data);
    }

    public function store()
    {
        $promoModel = new PromoModel();
        
        $data = [
            'code' => strtoupper($this->request->getPost('code')),
            'discount_amount' => $this->request->getPost('discount_amount'),
            'discount_type' => $this->request->getPost('discount_type'),
            'valid_until' => $this->request->getPost('valid_until'),
            'status' => 'active'
        ];

        $promoModel->insert($data);
        session()->setFlashdata('success', 'Kode Promo berhasil ditambahkan!');
        return redirect()->to('/admin/promos');
    }

    public function toggle($id)
    {
        $promoModel = new PromoModel();
        $promo = $promoModel->find($id);
        if ($promo) {
            $newStatus = $promo['status'] == 'active' ? 'inactive' : 'active';
            $promoModel->update($id, ['status' => $newStatus]);
        }
        return redirect()->to('/admin/promos');
    }

    public function delete($id)
    {
        $promoModel = new PromoModel();
        $promoModel->delete($id);
        session()->setFlashdata('success', 'Promo berhasil dihapus!');
        return redirect()->to('/admin/promos');
    }
}
