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

    public function profile()
    {
        $this->requireLogin();

        $user = User::findById($_SESSION['user']['id']);

        if (!$user) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'circle-alert',
                'title' => 'Profile unavailable',
                'message' => 'Please login again to continue.'
            ];
            header('Location: ?route=logout');
            exit;
        }

        $_SESSION['user'] = $user;
        $this->view('auth/profile', ['user' => $user]);
    }

    public function updateProfile()
    {
        $this->requireLogin();

        $id = $_SESSION['user']['id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'circle-alert',
                'title' => 'Update failed',
                'message' => 'Please enter a valid name and email.'
            ];
            header('Location: ?route=profile#account-info');
            exit;
        }

        if (User::emailExistsForOtherUser($email, $id)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'mail-warning',
                'title' => 'Email already used',
                'message' => 'Please use a different email address.'
            ];
            header('Location: ?route=profile#account-info');
            exit;
        }

        $profilePhoto = null;
        if (!empty($_FILES['profile_photo']['name']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['profile_photo']['tmp_name']);

            if (!isset($allowed[$mime])) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'icon' => 'image-off',
                    'title' => 'Photo not uploaded',
                    'message' => 'Please upload a JPG, PNG, or WEBP image.'
                ];
                header('Location: ?route=profile#account-info');
                exit;
            }

            if ($_FILES['profile_photo']['size'] > 3 * 1024 * 1024) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'icon' => 'file-warning',
                    'title' => 'Photo too large',
                    'message' => 'Please upload an image below 3MB.'
                ];
                header('Location: ?route=profile#account-info');
                exit;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/profiles';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $filename = 'profile_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            $target = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                $profilePhoto = 'uploads/profiles/' . $filename;
            }
        }

        User::updateProfile($id, $name, $email, $profilePhoto);
        $_SESSION['user'] = User::findById($id);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'badge-check',
            'title' => 'Profile updated',
            'message' => 'Your account details were saved.'
        ];

        header('Location: ?route=profile#account-info');
        exit;
    }

    public function updatePassword()
    {
        $this->requireLogin();

        $id = $_SESSION['user']['id'];
        $user = User::findById($id);

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$user || !password_verify($current, $user['password'])) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'key-round',
                'title' => 'Wrong password',
                'message' => 'Your current password is incorrect.'
            ];
            header('Location: ?route=profile#password-settings');
            exit;
        }

        if (strlen($new) < 6 || $new !== $confirm) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'icon' => 'shield-alert',
                'title' => 'Password not changed',
                'message' => 'Use at least 6 characters and make sure both new passwords match.'
            ];
            header('Location: ?route=profile#password-settings');
            exit;
        }

        User::updatePassword($id, $new);

        $_SESSION['flash'] = [
            'type' => 'success',
            'icon' => 'lock-keyhole',
            'title' => 'Password updated',
            'message' => 'Your password has been changed successfully.'
        ];

        header('Location: ?route=profile#password-settings');
        exit;
    }

}
