<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login');
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && Auth::verifyPassword($password, $user['password_hash'])) {
            Auth::login($user['id'], $user['email'], $user['name']);
            $userModel->updateLastLogin($user['id']);

            $this->setFlash('success', 'Login successful!');
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Invalid email or password');
            $this->redirect('/auth/login');
        }
    }

    public function register(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/register');
    }

    public function handleRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/register');
        }

        if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/auth/register');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = $this->validate(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => 'required|min:2', 'email' => 'required|email', 'password' => 'required|min:6']
        );

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = array_values($errors);
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            $this->redirect('/auth/register');
            return;
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->setFlash('error', 'An account with that email already exists.');
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            $this->redirect('/auth/register');
            return;
        }

        $userModel->create([
            'email' => $email,
            'password_hash' => Auth::hashPassword($password),
            'name' => $name
        ]);

        $this->setFlash('success', 'Account created! You can now log in.');
        $this->redirect('/auth/login');
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function forgotPassword(): void
    {
        $this->render('auth/forgot-password');
    }

    public function handleForgotPassword(): void
    {
        // To be implemented
        $this->setFlash('info', 'Password reset feature coming soon');
        $this->redirect('/auth/login');
    }

    public function resetPassword(): void
    {
        // To be implemented
        $this->render('auth/reset-password');
    }

    public function handleResetPassword(): void
    {
        // To be implemented
        $this->setFlash('info', 'Password reset feature coming soon');
        $this->redirect('/auth/login');
    }
}
