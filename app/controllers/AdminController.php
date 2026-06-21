<?php

class AdminController extends BaseController
{
    public function index()
    {
        $this->requireAdmin();
        $reports = Report::pending();
        $stats = Report::stats();
        $closeRequests = Report::pendingCloseRequests();
        $analytics = Report::analytics();
        $this->view('admin/index', compact('reports', 'stats', 'closeRequests', 'analytics'));
    }

    public function analytics()
    {
        $this->requireAdmin();
        $analytics = Report::analytics();
        $this->view('admin/analytics', compact('analytics'));
    }

    public function status()
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? 'active';
        $approved = $_POST['approved'] ?? 0;
        $report = !empty($_POST['id']) ? Report::find($_POST['id']) : null;
        Report::updateStatus($_POST['id'], $status, $approved);
        if ($report) {
            Report::addActivity($_POST['id'], $_SESSION['user']['id'], $approved ? 'badge-check' : 'clock-3', $approved ? 'Report Approved' : 'Report Updated', 'Status: ' . ucfirst($status));
            Report::notify($report['user_id'], $_POST['id'], 'admin', $approved ? 'Report approved' : 'Report updated', 'Admin updated your report status to ' . ucfirst($status) . '.');
        }
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => $approved ? 'badge-check' : 'clock-3',
            'title' => $approved ? 'Report approved' : 'Report updated',
            'message' => 'The report status was saved successfully.'
        ];
        header('Location: ?route=admin');
        exit;
    }

    public function closeRequest()
    {
        $this->requireAdmin();
        $id = $_POST['id'] ?? null;
        $action = $_POST['action_type'] ?? 'reject';
        $request = $id ? Report::findCloseRequest($id) : null;
        if ($request && $action === 'approve') {
            Report::updateStatus($request['report_id'], $request['result_status'], 1);
            Report::updateCloseRequest($id, 'approved');
            $report = Report::find($request['report_id']);
            Report::addActivity($request['report_id'], $_SESSION['user']['id'], 'party-popper', 'Case Updated', 'Admin approved a request and marked this case as ' . $request['result_status'] . '.');
            if ($report) Report::notify($report['user_id'], $request['report_id'], 'case', 'Case updated', 'Your report was marked as ' . ucfirst($request['result_status']) . '.');
        } elseif ($request) {
            Report::updateCloseRequest($id, 'rejected');
            $report = Report::find($request['report_id']);
            Report::addActivity($request['report_id'], $_SESSION['user']['id'], 'x-circle', 'Verification Rejected', 'Admin rejected a close/reunited request.');
            if ($report) Report::notify($report['user_id'], $request['report_id'], 'case', 'Verification rejected', 'A verification request for your report was rejected.');
        }
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => $action === 'approve' ? 'badge-check' : 'x-circle',
            'title' => $action === 'approve' ? 'Case updated' : 'Request rejected',
            'message' => $action === 'approve' ? 'The report was updated based on the submitted proof.' : 'The close request was rejected.'
        ];
        header('Location: ?route=admin');
        exit;
    }

    public function delete()
    {
        $this->requireAdmin();
        if (!empty($_POST['id'])) Report::delete($_POST['id']);
        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'trash-2',
            'title' => 'Report deleted',
            'message' => 'The selected report was removed successfully.'
        ];
        header('Location: ?route=admin');
        exit;
    }
}
