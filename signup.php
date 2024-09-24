<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('backkground.jpg'); /* Replace 'background.jpg' with your image path */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
        }
        .logo {
            width: 80px; /* Adjust width as needed */
            height: auto; /* Maintain aspect ratio */
            display: block;
            margin: 0 auto 15px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <img src="stoorelogo.png" alt="Logo" class="logo mb-3"> <!-- Replace 'storelogo.png' with your logo path -->
        <h1 class="text-center">Sign Up</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') : ?>
            <?php
            include 'connect.php';

            $username = $_POST["username"];
            $password = $_POST["password"];

            // Hash password before storing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Establish connection
            $con = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);

            // Check connection and handle errors
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "SELECT * FROM `registration` WHERE username='$username'";
            $result = mysqli_query($con, $sql);

            if ($result) {
                $num = mysqli_num_rows($result);
                if ($num > 0) {
                    // User already exists, redirect to login.php
                    header("Location: login.php");
                    exit();
                } else {
                    $sql = "INSERT INTO `registration`(username, password) VALUES('$username','$hashed_password')";
                    $result = mysqli_query($con, $sql);
                    if ($result) {
                        echo '<div class="alert alert-success mt-3" role="alert">Signup successful</div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3" role="alert">Error: ' . mysqli_error($con) . '</div>';
                    }
                }
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert">Error: ' . mysqli_error($con) . '</div>';
            }

            // Close connection
            mysqli_close($con);
            ?>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>
        <div class="login-link mt-3 text-center">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
