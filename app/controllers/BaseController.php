<?php

class BaseController
{
    protected function view($view, $data = [])
    {
        extract($data);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function requireLogin()
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'lock-keyhole',
                'title' => 'Login required',
                'message' => 'Please login first to continue.'
            ];

            header('Location: ?route=login');
            exit;
        }
    }

    protected function requireAdmin()
    {
        $this->requireLogin();

        if ($_SESSION['user']['role'] !== 'admin') {
            exit('Admin only');
        }
    }
}
