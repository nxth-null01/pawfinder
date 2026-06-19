<?php

class AdminController extends BaseController
{
    public function index()
    {
        $this->requireAdmin();

        $reports = Report::pending();
        $stats = Report::stats();

        $this->view('admin/index', compact('reports', 'stats'));
    }

    public function status()
    {
        $this->requireAdmin();

        $status = $_POST['status'] ?? 'active';
        $approved = $_POST['approved'] ?? 0;
        Report::updateStatus($_POST['id'], $status, $approved);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => $approved ? 'badge-check' : 'clock-3',
            'title' => $approved ? 'Report approved' : 'Report updated',
            'message' => 'The report status was saved successfully.'
        ];

        header('Location: ?route=admin');
        exit;
    }
}
