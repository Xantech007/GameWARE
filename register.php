<?php include "inc/header.php"; ?>

<?php
if($_POST){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
    $stmt->execute([$username,$email,$password]);

    echo "Registered successfully <a href='login.php'>Login</a>";
}
?>

<form method="POST">
<input name="username" placeholder="Username" required><br>
<input name="email" type="email" placeholder="Email" required><br>
<input name="password" type="password" placeholder="Password" required><br>
<button>Register</button>
</form>

<?php include "inc/footer.php"; ?>
