<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/database.php";
include "inc/countries.php";

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT full_name, username, email, phone, gender, address, country FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die("User not found");
}

/* UPDATE PROFILE */
$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $phone     = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $gender    = $_POST['gender'] ?? null;
    $address   = trim($_POST['address']);
    $country   = $_POST['country'] ?? null;

    try{

        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name=?, username=?, email=?, phone=?, gender=?, address=?, country=? 
            WHERE id=?
        ");

        $stmt->execute([
            $full_name,
            $username,
            $email,
            $phone,
            $gender,
            $address,
            $country,
            $user_id
        ]);

        $success = "Profile updated successfully";

        // Refresh user data
        $stmt = $conn->prepare("SELECT full_name, username, email, phone, gender, address, country FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<?php include "inc/header.php"; ?>
<?php include "inc/navbar.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container{
    max-width:800px;
    margin:auto;
    padding:20px;
}

.profile-box{
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    margin-top:40px;
}

.profile-box h2{
    margin-bottom:20px;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
}

.btn{
    padding:10px 18px;
    border:none;
    background:#00aaff;
    color:#fff;
    border-radius:6px;
    cursor:pointer;
}

.btn-dark{
    background:#222;
}

.success{
    color:green;
    margin-bottom:10px;
}

.error{
    color:red;
    margin-bottom:10px;
}
</style>

<div class="container">

<div class="profile-box">

<h2><i class="fa-solid fa-user"></i> My Profile</h2>

<?php if($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Full Name</label>
<input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
</div>

<div class="form-group">
<label>Gender</label>
<select name="gender">
    <option value="">Select</option>
    <option value="Male" <?php if($user['gender']=='Male') echo 'selected'; ?>>Male</option>
    <option value="Female" <?php if($user['gender']=='Female') echo 'selected'; ?>>Female</option>
</select>
</div>

<div class="form-group">
<label>Country</label>
<select name="country">
    <option value="">Select Country</option>
    <?php foreach($countries as $c): ?>
        <option value="<?php echo $c; ?>" <?php if($user['country']==$c) echo 'selected'; ?>>
            <?php echo $c; ?>
        </option>
    <?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label>Address</label>
<textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
</div>

<br>

<button type="submit" class="btn">
    <i class="fa-solid fa-save"></i> Save Changes
</button>

<a href="change-password.php" class="btn btn-dark" style="margin-left:10px;">
    <i class="fa-solid fa-key"></i> Change Password
</a>

</form>

</div>
</div>

<?php include "inc/footer.php"; ?>
