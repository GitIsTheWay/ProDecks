<?php
// login.php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

$page_title = "ورود";
$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "لطفا نام کاربری و رمز عبور را وارد کنید.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['experience'] = $user['experience'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "نام کاربری یا رمز عبور اشتباه است.";
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">ورود به ProDecks</h4>
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
                        <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ورود</button>
                </form>

                <div class="text-center mt-3">
                    <a href="register.php">حساب کاربری ندارید؟ ثبت نام کنید</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>