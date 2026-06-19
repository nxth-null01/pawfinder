<?php

class AuthController extends BaseController
{
    public function login()
    {
        $this->view('auth/login');
    }

    public function register()
    {
        $this->view('auth/register');
    }

    public function loginPost()
    {
        $user = User::findByEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['flash'] = [
                'type' => 'success',
                'icon' => 'badge-check',
                'title' => 'Login successful',
                'message' => 'Welcome back, ' . htmlspecialchars($user['name']) . '!'
            ];

            header('Location: ?route=dashboard');
            exit;
        }

        $_SESSION['flash'] = [
            'type' => 'error',
            'icon' => 'circle-alert',
            'title' => 'Login failed',
            'message' => 'Please check your email and password.'
        ];

        header('Location: ?route=login');
        exit;
    }

    public function registerPost()
    {
        User::create($_POST['name'], $_POST['email'], $_POST['password']);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'sparkles',
            'title' => 'Account created',
            'message' => 'You can now login to PawFinder.'
        ];

        header('Location: ?route=login');
        exit;
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'log-out',
            'title' => 'Logout successful',
            'message' => 'You have been safely logged out.'
        ];

        header('Location: ?route=home');
        exit;
    }
}
