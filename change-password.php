<?php
/* SESSION SAFE START */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* HANDLE PASSWORD CHANGE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } 
        elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } 
        elseif ($new_password !== $confirm_password) {
            $error = "New password and confirmation do not match.";
        } 
        else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);

            // Set success message in session and redirect
            $_SESSION['success_message'] = "Password changed successfully!";
            header("Location: profile.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<?php include "inc/header.php"; ?>
<?php include "inc/navbar.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container {
    max-width: 600px;
    margin: auto;
    padding: 20px;
}

.password-box {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin-top: 40px;
}

.password-box h2 {
    margin-bottom: 25px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
}

.form-group input:focus {
    outline: none;
    border-color: #00aaff;
    box-shadow: 0 0 0 3px rgba(0,170,255,0.1);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #00aaff;
    color: #fff;
}

.btn-secondary {
    background: #666;
    color: #fff;
    text-decoration: none;
}

.error {
    color: red;
    background: #ffe6e6;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}
</style>

<div class="container">
    <div class="password-box">
        <h2><i class="fa-solid fa-key"></i> Change Password</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
            </div>

            <br>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Password
            </button>

            <a href="profile.php" class="btn btn-secondary" style="margin-left: 10px;">
                <i class="fa-solid fa-arrow-left"></i> Cancel
            </a>
        </form>
    </div>
</div>

<?php include "inc/footer.php"; ?>
