<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\BannerModel;

class Banners extends BaseController
{
    public function index()
    {
        $bannerModel = new BannerModel();
        
        $data = [
            'title' => 'Manajemen Banner Aplikasi | Maijek Admin',
            'banners' => $bannerModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('admin/banners/index', $data);
    }

    public function create()
    {
        $bannerModel = new BannerModel();
        $file = $this->request->getFile('image');
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/banners', $newName);

            $bannerModel->insert([
                'title' => $this->request->getPost('title'),
                'image_path' => $newName,
                'status' => $this->request->getPost('status')
            ]);
            return redirect()->to('/admin/banners')->with('success', 'Banner berhasil diunggah.');
        }
        
        return redirect()->to('/admin/banners')->with('error', 'Gagal mengunggah banner.');
    }

    public function delete($id)
    {
        $bannerModel = new BannerModel();
        $banner = $bannerModel->find($id);
        if ($banner && file_exists('uploads/banners/' . $banner['image_path'])) {
            unlink('uploads/banners/' . $banner['image_path']);
        }
        $bannerModel->delete($id);

        return redirect()->to('/admin/banners')->with('success', 'Banner berhasil dihapus.');
    }
}
