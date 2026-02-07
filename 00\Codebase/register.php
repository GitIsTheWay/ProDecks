<?php
// register.php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

$page_title = "ثبت نام";
$errors = [];
$username = $email = $team_name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $team_name = trim($_POST['team_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) {
        $errors[] = "نام کاربری الزامی است.";
    } elseif (strlen($username) < 3) {
        $errors[] = "نام کاربری باید حداقل 3 کاراکتر باشد.";
    }

    if (empty($email)) {
        $errors[] = "ایمیل الزامی است.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "فرمت ایمیل نامعتبر است.";
    }

    if (empty($team_name)) {
        $errors[] = "نام تیم/گروه الزامی است.";
    }

    if (empty($password)) {
        $errors[] = "رمز عبور الزامی است.";
    } elseif (strlen($password) < 6) {
        $errors[] = "رمز عبور باید حداقل 6 کاراکتر باشد.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "رمز عبور و تکرار آن یکسان نیستند.";
    }

    if (empty($errors)) {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "نام کاربری یا ایمیل قبلا ثبت شده است.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, team_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $team_name, $team_name]);
            
            // Auto login after registration
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['team_name'] = $team_name;
            $_SESSION['level'] = 1;
            $_SESSION['experience'] = 0;
            
            header("Location: onboarding.php");
            exit;
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">ثبت نام در ProDecks</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">نام کاربری</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="team_name" class="form-label">نام تیم/شرکت/گروه</label>
                        <input type="text" class="form-control" id="team_name" name="team_name" value="<?php echo htmlspecialchars($team_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تکرار رمز عبور</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ثبت نام</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php">قبلا ثبت نام کرده‌اید؟ وارد شوید</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>