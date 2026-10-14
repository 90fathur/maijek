<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\TicketModel;

class Tickets extends BaseController
{
    public function index()
    {
        $ticketModel = new TicketModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $ticketModel->select('tickets.*, users.name as user_name, drivers.name as driver_name')
            ->join('users', 'users.id = tickets.user_id', 'left')
            ->join('drivers', 'drivers.id = tickets.driver_id', 'left');

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('tickets.subject', $q)
                ->orLike('users.name', $q)
                ->orLike('drivers.name', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('tickets.id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($status !== '' && in_array($status, ['open', 'closed'])) {
            $builder = $builder->where('tickets.status', $status);
        }

        $tickets = $builder->orderBy('tickets.status', 'DESC')
            ->orderBy('tickets.created_at', 'DESC')
            ->paginate($perPage);
        $pager = $ticketModel->pager;

        $data = [
            'title'      => 'Tiket Bantuan (Helpdesk) | Maijek Admin',
            'tickets'    => $tickets,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'status'     => $status,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/tickets/index', $data);
    }

    public function show($id)
    {
        $ticketModel = new TicketModel();
        $ticket = $ticketModel->find($id);
        if (!$ticket) return redirect()->to('/admin/tickets');

        $db = \Config\Database::connect();
        $replies = $db->table('ticket_replies')->where('ticket_id', $id)->orderBy('created_at', 'ASC')->get()->getResultArray();

        $data = [
            'title' => 'Balas Tiket | Maijek Admin',
            'ticket' => $ticket,
            'replies' => $replies
        ];

        return view('admin/tickets/show', $data);
    }

    public function reply($id)
    {
        $db = \Config\Database::connect();
        $db->table('ticket_replies')->insert([
            'ticket_id' => $id,
            'sender' => 'admin',
            'message' => $this->request->getPost('message'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($this->request->getPost('close_ticket')) {
            $ticketModel = new TicketModel();
            $ticketModel->update($id, ['status' => 'closed']);
        }

        return redirect()->to('/admin/tickets/show/' . $id)->with('success', 'Balasan terkirim.');
    }
}
