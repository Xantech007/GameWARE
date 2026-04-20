<?php 
include "inc/header.php";

$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($email && $password){

        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['balance'] = $user['balance'];

            header("Location: index.php");
            exit;
        } else {
            $message = "<div class='error'>
                <i class='fa-solid fa-circle-exclamation'></i> Invalid email or password
            </div>";
        }

    } else {
        $message = "<div class='error'>All fields are required</div>";
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.auth-wrapper{
    min-height:80vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f5fbff;
}

.auth-card{
    width:100%;
    max-width:400px;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    text-align:center;
}

/* LOGO */
.logo img{
    width:60px;
    margin-bottom:10px;
}

/* TITLE */
.auth-card h2{
    margin-bottom:5px;
}

.subtitle{
    color:#777;
    font-size:14px;
    margin-bottom:20px;
}

/* INPUTS */
.input-group{
    position:relative;
    margin-bottom:15px;
}

.input-group i{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#999;
}

.input-group input{
    width:100%;
    padding:10px 10px 10px 35px;
    border:1px solid #ddd;
    border-radius:6px;
    outline:none;
    transition:0.2s;
}

.input-group input:focus{
    border-color:#00aaff;
}

/* BUTTON */
.btn{
    width:100%;
    padding:10px;
    border:none;
    border-radius:6px;
    background:#00aaff;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.btn:hover{
    background:#008ecc;
}

/* MESSAGES */
.error{
    background:#ffe6e6;
    color:#c00;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
}

/* FOOT */
.bottom-text{
    margin-top:15px;
    font-size:14px;
}

.bottom-text a{
    color:#00aaff;
    text-decoration:none;
}
</style>

<div class="auth-wrapper">

<div class="auth-card">

    <!-- LOGO -->
    <div class="logo">
        <img src="assets/images/logo.png" alt="Logo">
    </div>

    <h2>Welcome Back</h2>
    <div class="subtitle">Login to continue playing & earning</div>

    <?php echo $message; ?>

    <form method="POST">

        <div class="input-group">
            <i class="fa-solid fa-envelope"></i>
            <input name="email" type="email" placeholder="Email Address" required>
        </div>

        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input name="password" type="password" placeholder="Password" required>
        </div>

        <button class="btn">
            <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>

    </form>

    <div class="bottom-text">
        Don’t have an account? <a href="register.php">Register</a>
    </div>

</div>

</div>

<?php include "inc/footer.php"; ?>
