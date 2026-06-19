<?php

class ReportController extends BaseController
{
    public function index()
    {
        $reports = Report::all(
            $_GET['q'] ?? '',
            $_GET['type'] ?? '',
            $_GET['status'] ?? ''
        );

        $this->view('reports/index', compact('reports'));
    }

    public function show()
    {
        $report = Report::find($_GET['id']);
        $sightings = Report::sightings($_GET['id']);

        $this->view('reports/show', compact('report', 'sightings'));
    }

    public function create()
    {
        $this->requireLogin();
        $this->view('reports/create');
    }

    public function dashboard()
    {
        $this->requireLogin();

        $reports = Report::byUser($_SESSION['user']['id']);

        $this->view('reports/dashboard', compact('reports'));
    }

    private function uploadImage(string $field, string $prefix): string
    {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            return '';
        }

        $fileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $fileName);

        return $fileName;
    }

    public function store()
    {
        $this->requireLogin();

        $data = $_POST;
        $data['user_id'] = $_SESSION['user']['id'];
        $data['photo'] = $this->uploadImage('photo', 'pet');

        Report::create($data);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'send',
            'title' => 'Report submitted',
            'message' => 'Your report was sent for admin approval.'
        ];

        header('Location: ?route=dashboard');
        exit;
    }

    public function storeSighting()
    {
        $data = $_POST;
        $data['photo'] = $this->uploadImage('sighting_photo', 'sighting');

        Report::addSighting($data);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'map-pin-check',
            'title' => 'Sighting submitted',
            'message' => 'Thank you. Your update can help reunite this animal.'
        ];

        header('Location: ?route=report-show&id=' . $_POST['report_id']);
        exit;
    }
}
