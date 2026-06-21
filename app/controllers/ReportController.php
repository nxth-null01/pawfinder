<?php

class ReportController extends BaseController
{
    public function index()
    {
        $reports = Report::all($_GET['q'] ?? '', $_GET['type'] ?? '', $_GET['status'] ?? '');
        $this->view('reports/index', compact('reports'));
    }

    public function show()
    {
        $report = Report::find($_GET['id']);
        if (!$report) { http_response_code(404); exit('Report not found'); }

        $sightings = Report::sightings($_GET['id']);
        $closeRequests = Report::closeRequests($_GET['id']);
        $comments = Report::comments($_GET['id']);
        $timeline = Report::timeline($_GET['id']);

        $this->view('reports/show', compact('report', 'sightings', 'closeRequests', 'comments', 'timeline'));
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
        $notifications = Report::notifications($_SESSION['user']['id'], 8);
        $this->view('reports/dashboard', compact('reports', 'notifications'));
    }

    public function notifications()
    {
        $this->requireLogin();
        $notifications = Report::notifications($_SESSION['user']['id'], 30);
        $this->view('reports/notifications', compact('notifications'));
    }

    public function markNotificationsRead()
    {
        $this->requireLogin();
        Report::markNotificationsRead($_SESSION['user']['id']);
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'bell-check',
            'title' => 'Notifications updated',
            'message' => 'All notifications were marked as read.'
        ];
        header('Location: ?route=notifications');
        exit;
    }

    private function uploadImage(string $field, string $prefix): string
    {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) return '';
        $fileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $fileName);
        return $fileName;
    }

    public function store()
    {
        $this->requireLogin();
        $data = $_POST;
        $data['user_id'] = $_SESSION['user']['id'];
        $data['photo'] = $this->uploadImage('photo', 'pet');
        $reportId = Report::create($data);
        Report::addActivity($reportId, $_SESSION['user']['id'], 'clipboard-plus', 'Report Created', 'The report was submitted and is waiting for admin approval.');
        Report::notify($_SESSION['user']['id'], $reportId, 'report', 'Report submitted', 'Your report was received and is pending admin approval.');

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
        $report = Report::find($data['report_id']);
        Report::addActivity($data['report_id'], $_SESSION['user']['id'] ?? null, 'map-pin-check', 'Sighting Added', ($data['location'] ?? '') . ' • ' . ($data['note'] ?? ''));
        if ($report) {
            Report::notify($report['user_id'], $data['report_id'], 'sighting', 'New sighting reported', 'Someone submitted a sighting update for ' . ($report['animal_name'] ?: 'your report') . '.');
        }
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'map-pin-check',
            'title' => 'Sighting submitted',
            'message' => 'Thank you. Your update can help reunite this animal.'
        ];
        header('Location: ?route=report-show&id=' . $_POST['report_id']);
        exit;
    }

    public function storeComment()
    {
        $this->requireLogin();
        $reportId = $_POST['report_id'] ?? null;
        $comment = trim($_POST['comment'] ?? '');
        if ($reportId && $comment !== '') {
            Report::addComment([
                'report_id' => $reportId,
                'user_id' => $_SESSION['user']['id'],
                'name' => $_SESSION['user']['name'],
                'comment' => $comment
            ]);
            $report = Report::find($reportId);
            Report::addActivity($reportId, $_SESSION['user']['id'], 'message-circle', 'Comment Added', $comment);
            if ($report && (int)$report['user_id'] !== (int)$_SESSION['user']['id']) {
                Report::notify($report['user_id'], $reportId, 'comment', 'New comment on your report', $_SESSION['user']['name'] . ' commented on ' . ($report['animal_name'] ?: 'your report') . '.');
            }
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'message-circle',
                'title' => 'Comment posted',
                'message' => 'Your comment was added to the report.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }

    public function storeCloseRequest()
    {
        $data = $_POST;
        $data['proof_photo'] = $this->uploadImage('proof_photo', 'proof');
        Report::addCloseRequest($data);
        $report = Report::find($data['report_id']);
        Report::addActivity($data['report_id'], $_SESSION['user']['id'] ?? null, 'badge-check', 'Verification Request Submitted', $data['note'] ?? '');
        if ($report) {
            Report::notify($report['user_id'], $data['report_id'], 'verification', 'Verification request submitted', 'A request was submitted to mark your report as ' . $data['result_status'] . '.');
        }
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'badge-check',
            'title' => 'Request submitted',
            'message' => 'Admin will verify your proof before closing or marking this case as reunited.'
        ];
        header('Location: ?route=report-show&id=' . $_POST['report_id']);
        exit;
    }
}
