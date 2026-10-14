<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\OrderModel;

class Reviews extends BaseController
{
    public function index()
    {
        $orderModel = new OrderModel();
        $q = trim($this->request->getGet('q') ?? '');
        $rating = trim($this->request->getGet('rating') ?? '');
        $perPage = 15;

        $builder = $orderModel->select('orders.*, users.name as user_name, drivers.name as driver_name')
            ->join('users', 'users.id = orders.user_id')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->where('orders.rating IS NOT NULL');

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('users.name', $q)
                ->orLike('drivers.name', $q)
                ->orLike('orders.review_comment', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('orders.id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($rating !== '' && is_numeric($rating)) {
            $builder = $builder->where('orders.rating', (int)$rating);
        }

        $reviews = $builder->orderBy('orders.created_at', 'DESC')->paginate($perPage);
        $pager = $orderModel->pager;

        $data = [
            'title'      => 'Ulasan & Rating | Maijek Admin',
            'reviews'    => $reviews,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'rating'     => $rating,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/reviews/index', $data);
    }
}
