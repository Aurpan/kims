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

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';

        $errors = $this->validate([
            'email' => $email,
            'password' => $password,
            'name' => $name
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'name' => 'required|min:2'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/auth/register');
            return;
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->setFlash('error', 'Email already exists');
            $this->redirect('/auth/register');
            return;
        }

        $userModel->create([
            'email' => $email,
            'password_hash' => Auth::hashPassword($password),
            'name' => $name
        ]);

        $this->setFlash('success', 'Registration successful! Please log in.');
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
