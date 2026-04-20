<?php include "../inc/header.php"; ?>
<?php include "../inc/navbar.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container{
    max-width:900px;
    margin:auto;
    padding:20px;
    text-align:center;
}

.game-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
}

/* CANVAS stays dark for contrast */
canvas{
    border:2px solid #ddd;
    border-radius:12px;
    background:#000;
    width:100%;
    max-width:500px;
}

/* BUTTONS */
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
    border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    display:none;
    z-index:999;
    text-align:center;
}
</style>

<div class="container">

<h2><i class="fa-solid fa-bullseye"></i> Bubble Shooter</h2>
<p style="color:#666;">Match bubbles, score points, and earn rewards</p>

<div class="game-box">

<div style="margin-bottom:10px;">
    Score: <strong id="score">0</strong>
</div>

<canvas id="game"></canvas>

<br>
<button onclick="restartGame()"><i class="fa-solid fa-rotate"></i> Restart</button>
<button onclick="toggleSound()" id="soundBtn"><i class="fa-solid fa-volume-high"></i></button>

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

// ===== RESPONSIVE =====
function resize(){
    let s = Math.min(window.innerWidth/BASE_W, window.innerHeight/BASE_H);
    canvas.style.width = BASE_W*s+"px";
    canvas.style.height = BASE_H*s+"px";
}
window.addEventListener("resize",resize);
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
    if(sound){ bg.play().catch(()=>{}); soundBtn.innerHTML='<i class="fa-solid fa-volume-high"></i>';}
    else{ bg.pause(); soundBtn.innerHTML='<i class="fa-solid fa-volume-xmark"></i>';}
}

// ===== GAME =====
const SIZE=22, ROWS=10, COLS=9;
let grid=[],score=0,gameOver=false;
let shooter={x:210,y:480,angle:0};
let current,next;
let colors=["#ff4d4d","#4dff4d","#4d4dff","#ffff4d","#ff4dff","#4dffff"];

let effects=[],stars=[];
for(let i=0;i<60;i++){
    stars.push({x:Math.random()*BASE_W,y:Math.random()*BASE_H,s:Math.random()*2});
}

function rand(){return colors[Math.floor(Math.random()*colors.length)];}

// INIT
function init(){
    grid=[];
    for(let r=0;r<ROWS;r++){
        grid[r]=[];
        for(let c=0;c<COLS;c++){
            grid[r][c]=(r<5)?rand():null;
        }
    }
}

// DRAW BG
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

// DRAW BUBBLE
function drawBubble(x,y,color){
    ctx.beginPath();
    ctx.arc(x,y,SIZE,0,Math.PI*2);
    ctx.fillStyle=color;
    ctx.fill();
}

// DRAW
function draw(){
    drawBG();

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]){
                drawBubble(c*45+40,r*45+40,grid[r][c]);
            }
        }
    }

    if(current) drawBubble(current.x,current.y,current.color);

    if(gameOver){
        sendEarnings();

        ctx.fillStyle="rgba(0,0,0,0.7)";
        ctx.fillRect(0,0,canvas.width,canvas.height);
        ctx.fillStyle="#fff";
        ctx.fillText("GAME OVER",150,250);
    }
}

// ===== EARNINGS (UPDATED SYSTEM) =====
function sendEarnings(){
    if(submitted) return;
    submitted = true;

    let duration = Math.floor((Date.now() - startTime)/1000);
    if(duration < 3) return;

    let earned = (duration * 0.02).toFixed(2); // simple rate

    fetch("../save_earnings.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({
            game:"Bubble Shooter",
            amount: earned
        })
    });

    let popup=document.getElementById("popup");
    popup.style.display="block";

    popup.innerHTML = `
        <h3><i class="fa-solid fa-coins"></i> Earnings</h3>
        <p>You earned <strong>${earned}</strong></p>
        <p style="color:#666;">Login required to withdraw</p>
    `;
}

// UPDATE
function update(){
    if(!current || !current.speed || gameOver) return;

    current.x += Math.cos(current.angle)*current.speed;
    current.y += Math.sin(current.angle)*current.speed;

    if(current.y < 40) gameOver=true;
}

// SHOOT
function shoot(){
    if(current.speed||gameOver)return;
    current.speed=10;
    current.angle=shooter.angle;
}

// CONTROLS
canvas.addEventListener("mousemove",e=>{
    let r=canvas.getBoundingClientRect();
    let x=e.clientX-r.left;
    let y=e.clientY-r.top;
    shooter.angle=Math.atan2(y-480,x-210);
});

canvas.addEventListener("click",shoot);

// RESTART
function restartGame(){
    score=0;
    gameOver=false;
    submitted=false;
    startTime=Date.now();
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
    update();
    draw();
    requestAnimationFrame(loop);
}
loop();
</script>

<?php include "../inc/footer.php"; ?>
