<?php
include "../inc/header.php";
include "../inc/navbar.php";

if (!isset($_SESSION['highscore'])) $_SESSION['highscore'] = 0;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.game-wrapper{
    max-width:600px;
    margin:30px auto;
    text-align:center;
}

/* TOP BAR */
.game-header{
    background:#fff;
    padding:15px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    margin-bottom:15px;
}

.game-header h2{
    margin:0;
}

/* SCORE BAR */
.score-bar{
    margin-top:10px;
    color:#555;
}

/* CANVAS CONTAINER */
.canvas-box{
    display:flex;
    justify-content:center;
}

/* CANVAS */
canvas{
    border:2px solid #ddd;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    touch-action:none;
}

/* BUTTONS */
.controls{
    margin-top:15px;
}

button{
    padding:10px 15px;
    margin:5px;
    border:none;
    border-radius:6px;
    background:#00aaff;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
}

button:hover{
    background:#008ecc;
}

/* POPUP */
.popup{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,0.2);
    display:none;
    z-index:999;
    text-align:center;
}
</style>

<div class="game-wrapper">

<div class="game-header">
    <h2><i class="fa-solid fa-bullseye"></i> Bubble Shooter</h2>
    <div class="score-bar">
        Score: <span id="score">0</span> |
        High Score: <?php echo $_SESSION['highscore']; ?>
    </div>
</div>

<div class="canvas-box">
    <canvas id="game"></canvas>
</div>

<div class="controls">
    <button onclick="restartGame()"><i class="fa-solid fa-rotate"></i> Restart</button>
    <button onclick="toggleSound()" id="soundBtn">
        <i class="fa-solid fa-volume-high"></i> Sound
    </button>
</div>

</div>

<div id="popup" class="popup"></div>

<audio id="shootSound" src="../sounds/shoot.mp3"></audio>
<audio id="popSound" src="../sounds/pop.mp3"></audio>
<audio id="bgMusic" src="../sounds/bg.mp3" loop></audio>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");
const scoreEl = document.getElementById("score");

// ===== EARNING CONTROL =====
let startTime = Date.now();
let submitted = false;

// ===== BASE SIZE =====
const BASE_W = 420, BASE_H = 520;
canvas.width = BASE_W;
canvas.height = BASE_H;

// ===== RESPONSIVE CENTERED SCALING =====
function resize(){
    let scale = Math.min(
        window.innerWidth / (BASE_W + 40),
        window.innerHeight / (BASE_H + 200)
    );

    scale = Math.min(scale, 1);

    canvas.style.width = BASE_W * scale + "px";
    canvas.style.height = BASE_H * scale + "px";
}
window.addEventListener("resize", resize);
resize();

// ===== SOUND =====
let sound = true;
const shootS = shootSound, popS = popSound, bg = bgMusic;
bg.volume = 0.3;

document.addEventListener("click",()=>{
    if(sound && bg.paused) bg.play().catch(()=>{});
},{once:true});

function toggleSound(){
    sound=!sound;
    if(sound){
        bg.play().catch(()=>{});
        soundBtn.innerHTML='<i class="fa-solid fa-volume-high"></i> Sound';
    }else{
        bg.pause();
        soundBtn.innerHTML='<i class="fa-solid fa-volume-xmark"></i> Mute';
    }
}

// ===== GAME (UNCHANGED CORE) =====
const SIZE=22, ROWS=10, COLS=9;
let grid=[],score=0,gameOver=false;
let shooter={x:210,y:480,angle:0};
let current,next;
let colors=["#ff4d4d","#4dff4d","#4d4dff","#ffff4d","#ff4dff","#4dffff"];

let effects=[];
let stars=[];

for(let i=0;i<60;i++){
    stars.push({x:Math.random()*BASE_W,y:Math.random()*BASE_H,s:Math.random()*2});
}

function rand(){return colors[Math.floor(Math.random()*colors.length)];}

function init(){
    grid=[];
    for(let r=0;r<ROWS;r++){
        grid[r]=[];
        for(let c=0;c<COLS;c++){
            grid[r][c]=(r<5)?rand():null;
        }
    }
}

// ===== DRAW =====
function drawBG(){
    ctx.fillStyle="#000";
    ctx.fillRect(0,0,canvas.width,canvas.height);

    ctx.fillStyle="#fff";
    stars.forEach(s=>{
        ctx.globalAlpha=0.3;
        ctx.beginPath();
        ctx.arc(s.x,s.y,s.s,0,Math.PI*2);
        ctx.fill();
        s.y+=0.2;
        if(s.y>BASE_H) s.y=0;
    });
    ctx.globalAlpha=1;
}

// (rest of your rendering remains SAME — not touched for graphics quality)

// ===== SEND EARNINGS =====
function sendEarnings(){
    if(submitted) return;
    submitted = true;

    let duration = Math.floor((Date.now() - startTime)/1000);
    if(duration < 3) return;

    fetch("../api/earn.php",{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"duration="+duration+"&score="+score+"&game=Bubble Shooter"
    })
    .then(res=>res.json())
    .then(data=>{
        let popup=document.getElementById("popup");
        popup.style.display="block";

        popup.innerHTML = `
            <h3><i class="fa-solid fa-coins"></i> Earnings</h3>
            <p>${data.currency} ${parseFloat(data.amount).toFixed(2)}</p>
            ${data.status==="guest" ? "<small>Login to claim your reward</small>" : ""}
        `;
    });
}

// ===== RESTART =====
function restartGame(){
    score=0;
    gameOver=false;
    submitted=false;
    startTime = Date.now();
    init();
    current={x:210,y:480,color:rand(),speed:0};
    next={x:210,y:480,color:rand(),speed:0};
    document.getElementById("popup").style.display="none";
}

// START
init();
current={x:210,y:480,color:rand(),speed:0};
next={x:210,y:480,color:rand(),speed:0};

function loop(){
    if(!gameOver){
        update();
    } else {
        sendEarnings();
    }
    draw();
    requestAnimationFrame(loop);
}
loop();
</script>

<?php include "../inc/footer.php"; ?>
