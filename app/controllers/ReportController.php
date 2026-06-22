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
        $commentSort = $_GET['comment_sort'] ?? 'newest';
        $comments = Report::comments($_GET['id'], $commentSort);
        $timeline = Report::timeline($_GET['id']);
        $similarReports = Report::similarReports($report);
        $isFollowing = !empty($_SESSION['user']) ? Report::isFollowing($_GET['id'], $_SESSION['user']['id']) : false;
        $trustScore = Report::trustScore($report['user_id']);

        $this->view('reports/show', compact('report', 'sightings', 'closeRequests', 'comments', 'timeline', 'similarReports', 'isFollowing', 'trustScore', 'commentSort'));
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
        $sightingId = Report::addSighting($data);
        $report = Report::find($data['report_id']);
        Report::addActivity($data['report_id'], $_SESSION['user']['id'] ?? null, 'map-pin-check', 'Sighting Added', ($data['location'] ?? '') . ' • ' . ($data['note'] ?? ''));
        if ($report) {
            Report::notify($report['user_id'], $data['report_id'], 'sighting', 'New sighting reported', 'Someone submitted a sighting update for ' . ($report['animal_name'] ?: 'your report') . '.');
            Report::notifyFollowers($data['report_id'], $_SESSION['user']['id'] ?? 0, 'sighting', 'New sighting reported', 'A community member added a new sighting for this case.');
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
        $type = ($_POST['comment_type'] ?? 'comment') === 'sighting' ? 'sighting' : 'comment';
        $sightingLocation = trim($_POST['sighting_location'] ?? '');
        if ($reportId && $comment !== '') {
            Report::addComment([
                'report_id' => $reportId,
                'user_id' => $_SESSION['user']['id'],
                'name' => $_SESSION['user']['name'],
                'comment' => $comment,
                'parent_id' => $_POST['parent_id'] ?? null,
                'type' => $type,
                'sighting_location' => $sightingLocation ?: null
            ]);
            $report = Report::find($reportId);

            if ($type === 'sighting' && $sightingLocation !== '') {
                Report::addSighting([
                    'report_id' => $reportId,
                    'name' => $_SESSION['user']['name'],
                    'contact' => $_SESSION['user']['email'] ?? '',
                    'location' => $sightingLocation,
                    'note' => $comment,
                    'photo' => ''
                ]);
                Report::addActivity($reportId, $_SESSION['user']['id'], 'map-pin-check', 'Sighting Reported', $sightingLocation . ' • ' . $comment);
                if ($report) {
                    Report::notify($report['user_id'], $reportId, 'sighting', 'New sighting reported', $_SESSION['user']['name'] . ' added a sighting update for ' . ($report['animal_name'] ?: 'your report') . '.');
                    Report::notifyFollowers($reportId, $_SESSION['user']['id'], 'sighting', 'New sighting reported', 'A community member added a sighting update for this case.');
                }
            } else {
                if ($report && (int)$report['user_id'] !== (int)$_SESSION['user']['id']) {
                    Report::notify($report['user_id'], $reportId, 'comment', 'New comment on your report', $_SESSION['user']['name'] . ' commented on ' . ($report['animal_name'] ?: 'your report') . '.');
                }
                Report::notifyFollowers($reportId, $_SESSION['user']['id'], 'comment', 'New comment added', $_SESSION['user']['name'] . ' joined the discussion on this case.');
            }
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => $type === 'sighting' ? 'map-pin-check' : 'message-circle',
                'title' => $type === 'sighting' ? 'Sighting update posted' : 'Comment posted',
                'message' => $type === 'sighting' ? 'Your sighting update was added to the case.' : 'Your comment was added to the report.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }


    public function updateComment()
    {
        $this->requireLogin();
        $commentId = $_POST['comment_id'] ?? null;
        $reportId = $_POST['report_id'] ?? ($commentId ? Report::commentReportId($commentId) : null);
        $comment = trim($_POST['comment'] ?? '');
        if ($commentId && $comment !== '') {
            $isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';
            Report::updateComment($commentId, $_SESSION['user']['id'], $comment, $isAdmin);
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'edit-3',
                'title' => 'Comment updated',
                'message' => 'Your comment was edited successfully.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }

    public function deleteComment()
    {
        $this->requireLogin();
        $commentId = $_POST['comment_id'] ?? null;
        $reportId = $_POST['report_id'] ?? ($commentId ? Report::commentReportId($commentId) : null);
        if ($commentId) {
            $isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';
            Report::deleteComment($commentId, $_SESSION['user']['id'], $isAdmin);
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'trash-2',
                'title' => 'Comment deleted',
                'message' => 'The comment was deleted, but replies are still visible.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }


    public function reportComment()
    {
        $this->requireLogin();
        $commentId = $_POST['comment_id'] ?? null;
        $reportId = $_POST['report_id'] ?? ($commentId ? Report::commentReportId($commentId) : null);
        $reason = trim($_POST['reason'] ?? 'Reported by user');
        if ($commentId) {
            Report::reportComment($commentId, $_SESSION['user']['id'], $reason);
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'flag',
                'title' => 'Comment reported',
                'message' => 'Thanks. Admin can review this comment.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }

    public function pinComment()
    {
        $this->requireLogin();
        $commentId = $_POST['comment_id'] ?? null;
        $reportId = $_POST['report_id'] ?? ($commentId ? Report::commentReportId($commentId) : null);
        $report = $reportId ? Report::find($reportId) : null;
        if ($commentId && $report && ((int)$report['user_id'] === (int)$_SESSION['user']['id'] || ($_SESSION['user']['role'] ?? '') === 'admin')) {
            Report::pinComment($commentId, $reportId, (int)($_POST['pin'] ?? 1));
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'pin',
                'title' => !empty($_POST['pin']) ? 'Comment pinned' : 'Comment unpinned',
                'message' => 'Pinned comments appear first in the discussion.'
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


    public function toggleHelpful()
    {
        $this->requireLogin();
        $commentId = $_POST['comment_id'] ?? null;
        $reportId = $_POST['report_id'] ?? ($commentId ? Report::commentReportId($commentId) : null);
        if ($commentId) Report::toggleHelpful($commentId, $_SESSION['user']['id']);
        header('Location: ?route=report-show&id=' . $reportId . '#comments');
        exit;
    }

    public function toggleFollow()
    {
        $this->requireLogin();
        $reportId = $_POST['report_id'] ?? null;
        if ($reportId) {
            $following = Report::toggleFollow($reportId, $_SESSION['user']['id']);
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => $following ? 'bell' : 'bell-off',
                'title' => $following ? 'Following case' : 'Unfollowed case',
                'message' => $following ? 'You will receive updates for this report.' : 'You will no longer receive follower updates for this report.'
            ];
        }
        header('Location: ?route=report-show&id=' . $reportId);
        exit;
    }

    public function verifySighting()
    {
        $this->requireLogin();
        $sightingId = $_POST['sighting_id'] ?? null;
        $reportId = $_POST['report_id'] ?? null;
        $report = $reportId ? Report::find($reportId) : null;
        if ($sightingId && $report && ((int)$report['user_id'] === (int)$_SESSION['user']['id'] || $_SESSION['user']['role'] === 'admin')) {
            Report::verifySighting($sightingId, 1);
            Report::addActivity($reportId, $_SESSION['user']['id'], 'badge-check', 'Verified Sighting', 'Owner/admin confirmed a submitted sighting.');
            Report::notifyFollowers($reportId, $_SESSION['user']['id'], 'verified', 'Sighting verified', 'A sighting was verified by the owner/admin.');
        }
        header('Location: ?route=report-show&id=' . $reportId . '#sightings');
        exit;
    }

}
