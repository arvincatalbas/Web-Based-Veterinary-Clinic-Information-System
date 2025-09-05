<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../public/css/forgot_password.css">
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
            <h1>Forgot Password</h1>
            <form action="#" method="POST">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="mail"></ion-icon>
                    </span>
                    <input type="email" name="email" required>
                    <label>Enter your registered email</label>
                </div>
                <button type="submit" class="btn">Send Reset Link</button>
                <div class="login-register">
                    <p>Remembered your password? <a href="index.php">Login</a></p>
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