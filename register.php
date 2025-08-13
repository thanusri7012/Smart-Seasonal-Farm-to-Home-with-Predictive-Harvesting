<?php
include 'includes/db_connection.php';

$name = $email = $phone_number = $password = $confirm_password = $user_type = "";
$name_err = $email_err = $phone_number_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter your name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";     
    } else{
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    if(empty(trim($_POST["phone_number"]))){
        $phone_number_err = "Please enter your phone number.";
    } else{
        $phone_number = trim($_POST["phone_number"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    $user_type = $_POST["user_type"];

    if(empty($name_err) && empty($email_err) && empty($phone_number_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO users (name, email, phone_number, password, user_type) VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("sssss", $param_name, $param_email, $param_phone_number, $param_password, $param_user_type);
            $param_name = $name;
            $param_email = $email;
            $param_phone_number = $phone_number;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_user_type = $user_type;
            
            if($stmt->execute()){
                header("location: login.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm" style="width: 28rem;">
            <h3 class="card-title text-center mb-4">Join Smart Farm 🌱</h3>
            <p class="text-center text-muted">Create your account to start buying or selling.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label class="form-label">Account Type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" value="buyer" checked>
                        <label class="form-check-label">Buyer</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" value="farmer">
                        <label class="form-check-label">Farmer</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                    <span class="invalid-feedback"><?php echo $name_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control <?php echo (!empty($phone_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone_number; ?>">
                    <span class="invalid-feedback"><?php echo $phone_number_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Register</button>
                </div>
                <p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-success">Login here</a>.</p>
            </form>
        </div>
    </div>
</body>
</html>