<?php include "inc/header.php"; ?>
<?php include "inc/navbar.php"; ?>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container{
    max-width:1150px;
    margin:auto;
    padding:20px;
}

/* HERO */
.hero{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    justify-content:space-between;
    gap:30px;
    margin-top:40px;
}

.hero-text{flex:1;}
.hero h1{font-size:38px;margin-bottom:15px;}
.hero p{color:#555;font-size:17px;line-height:1.6;}

.hero-img img{
    width:100%;
    max-width:420px;
}

/* SECTION */
.section{margin-top:70px;}
.section h2{margin-bottom:25px;}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.card{
    padding:25px;
    text-align:left;
}

.card i{
    font-size:28px;
    color:#00aaff;
    margin-bottom:10px;
}

/* STEPS */
.steps{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}

.step{
    flex:1;
    min-width:220px;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

/* REVIEWS */
.review{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.review i{
    color:#ffc107;
}

/* CTA */
.cta{
    text-align:center;
    padding:50px;
    background:#eaf6ff;
    border-radius:12px;
}
</style>

<div class="container">

<!-- HERO -->
<div class="hero">
    <div class="hero-text">
        <h1><i class="fa-solid fa-gamepad"></i> Play Games & Earn <?php echo $currency; ?></h1>
        <p>
            Enjoy fun, skill-based games while earning real rewards. 
            The more you play, the more you earn. Simple, fast, and secure payouts.
        </p>

        <br>

        <a href="games.php" class="btn">
            <i class="fa-solid fa-play"></i> Start Playing
        </a>

        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn" style="margin-left:10px;background:#222;">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </a>
        <?php endif; ?>
    </div>

    <div class="hero-img">
        <img src="assets/images/hero.png">
    </div>
</div>

<!-- HOW IT WORKS -->
<div class="section">
    <h2><i class="fa-solid fa-gears"></i> How It Works</h2>

    <div class="steps">
        <div class="step">
            <h3><i class="fa-solid fa-gamepad"></i> Play</h3>
            <p>Choose any game and start playing instantly. No downloads needed.</p>
        </div>

        <div class="step">
            <h3><i class="fa-solid fa-coins"></i> Earn</h3>
            <p>Earn <?php echo $currency; ?> based on your gameplay time and performance.</p>
        </div>

        <div class="step">
            <h3><i class="fa-solid fa-wallet"></i> Withdraw</h3>
            <p>Withdraw your earnings securely through supported payment methods.</p>
        </div>
    </div>
</div>

<!-- WHY CHOOSE US -->
<div class="section">
    <h2><i class="fa-solid fa-star"></i> Why Choose Us</h2>

    <div class="cards">
        <div class="card">
            <i class="fa-solid fa-bolt"></i>
            <h3>Instant Play</h3>
            <p>No downloads. No waiting. Just click and play.</p>
        </div>

        <div class="card">
            <i class="fa-solid fa-shield-halved"></i>
            <h3>Secure System</h3>
            <p>Your earnings and data are protected with secure backend systems.</p>
        </div>

        <div class="card">
            <i class="fa-solid fa-money-bill-wave"></i>
            <h3>Real Earnings</h3>
            <p>Convert your gameplay into real withdrawable money.</p>
        </div>
    </div>
</div>

<!-- FEATURED GAMES -->
<div class="section">
    <h2><i class="fa-solid fa-fire"></i> Featured Games</h2>

    <div class="cards">
        <div class="card">
            <h3><i class="fa-solid fa-car"></i> Race Car</h3>
            <p>Avoid traffic and survive as long as possible.</p>
            <a href="games/race-car.php" class="btn">Play</a>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-bullseye"></i> Bubble Shooter</h3>
            <p>Match bubbles and clear levels.</p>
            <a href="games/bubble.php" class="btn">Play</a>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="section">
    <h2><i class="fa-solid fa-chart-line"></i> Platform Stats</h2>

    <div class="cards">
        <div class="card">
            <h3>10,000+</h3>
            <p>Games Played</p>
        </div>

        <div class="card">
            <h3>5,000+</h3>
            <p>Active Users</p>
        </div>

        <div class="card">
            <h3><?php echo $currency; ?> 1,000,000+</h3>
            <p>Total Paid Out</p>
        </div>
    </div>
</div>

<!-- REVIEWS -->
<div class="section">
    <h2><i class="fa-solid fa-comments"></i> Player Reviews</h2>

    <div class="cards">

        <div class="review">
            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
            <p>“This platform is legit. I’ve already withdrawn twice!”</p>
            <small>- Daniel</small>
        </div>

        <div class="review">
            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
            <p>“Fun games and real rewards. Highly recommend.”</p>
            <small>- Sarah</small>
        </div>

        <div class="review">
            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
            <p>“Best play-to-earn experience I’ve used.”</p>
            <small>- Michael</small>
        </div>

    </div>
</div>

<!-- CTA -->
<div class="section">
    <div class="cta">
        <h2><i class="fa-solid fa-rocket"></i> Start Earning Today</h2>
        <p>Join thousands of players already earning <?php echo $currency; ?>.</p>

        <br>

        <a href="games.php" class="btn">
            <i class="fa-solid fa-play"></i> Start Playing
        </a>

        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn" style="margin-left:10px;background:#222;">
                <i class="fa-solid fa-user-plus"></i> Sign Up
            </a>
        <?php endif; ?>
    </div>
</div>

</div>

<?php include "inc/footer.php"; ?>
