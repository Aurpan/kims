<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kitzoholic Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .register-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            width: 100%;
            margin-top: 20px;
        }
        .btn-register:hover {
            background: linear-gradient(135deg, #5568d3 0%, #653a8a 100%);
            color: white;
        }
        .register-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .register-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .register-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-warehouse"></i> Kitzoholic</h2>
            <p>Create your account</p>
        </div>

        <?php
        if ($flash = \App\Core\Auth::getFlash() ?? false) {
            $alertType = $flash['type'] === 'error' ? 'danger' : $flash['type'];
            echo "<div class='alert alert-$alertType alert-dismissible fade show' role='alert'>";
            echo htmlspecialchars($flash['message']);
            echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
            echo "</div>";
        }

        if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
            foreach ($_SESSION['errors'] as $error) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
                echo htmlspecialchars($error);
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                echo "</div>";
            }
            unset($_SESSION['errors']);
        }
        ?>

        <form method="POST" action="/auth/register">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::generateCSRFToken() ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name"
                       placeholder="Enter your full name"
                       value="<?= htmlspecialchars($_SESSION['old']['name'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="Enter your email"
                       value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Min. 6 characters" required>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                       placeholder="Re-enter your password" required>
            </div>

            <?php unset($_SESSION['old']); ?>

            <button type="submit" class="btn btn-register">Create Account</button>
        </form>

        <div class="register-footer">
            <p>Already have an account? <a href="/auth/login">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
