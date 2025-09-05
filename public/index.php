<?php

include_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = trim($_POST['login_username']);
        $password = trim($_POST['login_password']);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['password'] = $user['password'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "";
        }
    } elseif (isset($_POST['register'])) {
        $username = trim($_POST['reg_username']);
        $email = trim($_POST['reg_email']);
        $password = trim($_POST['reg_password']);
        $confirm_password = trim($_POST['reg_confirm_password']);
        

        if ($password !== $confirm_password) {
            $reg_error = "";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $reg_error = "";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $reg_success = "";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BVC Sign In/Sign Up Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="vet-logo">
        <div>
            <img src="./img/vet-logo.png">
        </div>
        <div>
            <h1>Welcome!</h1>
            <p>Bulan Veterinary Clinic</p>
        </div>
    </div>
    <div class="wrapper">
        <div class="form-box login">
            <h1>Sign In</h1>
            <?php if (isset($login_error)): ?>
                <div class="error-message"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <?php if (isset($reg_success)): ?>
                <div class="success-message"><?php echo $reg_success; ?></div>
            <?php endif; ?>
            <form action="index.php" method="post">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="login_username" required>
                    <label>Username</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="lock-closed"></ion-icon>
                    </span>
                    <input type="password" name="login_password" required>
                    <label>Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember">Remember me</label>
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
                <button type="submit" name="login" class="btn">Sign In</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="#" class="register-link">Sign Up</a></p>
                </div>
            </form>
        </div>
        <div class="form-box register">
            <h1>Sign Up</h1>
            <?php if (isset($reg_error)): ?>
                <div class="error-message"><?php echo $reg_error; ?></div>
            <?php endif; ?>
            <form action="index.php" method="post">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="reg_username" required>
                    <label>Username</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="mail"></ion-icon>
                    </span>
                    <input type="email" name="reg_email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="lock-closed"></ion-icon>
                    </span>
                    <input type="password" name="reg_password" required>
                    <label>Password</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="lock-closed"></ion-icon>
                    </span>
                    <input type="password" name="reg_confirm_password" required>
                    <label>Confirm Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" name="terms" required>I agree to the terms & conditions</label>
                </div>
                <button type="submit" name="register" class="btn">Sign Up</button>
                <div class="login-register">
                    <p>Already have an account? <a href="#" class="login-link">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="../public/js/script.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>