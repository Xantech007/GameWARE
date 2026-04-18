<?php
session_start();
if (!isset($_SESSION['highscore'])) $_SESSION['highscore'] = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bubble Shooter Pro + Sound</title>

<style>
body{
    margin:0;
    background: radial-gradient(circle,#111,#000);
    font-family:Arial;
    text-align:center;
    color:#fff;
}
h1{margin:15px;}
canvas{
    background:#000;
    border:3px solid #444;
    border-radius:10px;
    box-shadow:0 0 20px #0ff;
}
.score{
    font-size:20px;
    margin:10px;
}
button{
    padding:10px 20px;
    border:none;
    border-radius:5px;
    background:#00c3ff;
    color:#000;
    font-weight:bold;
    cursor:pointer;
}
</style>
</head>

<body>

<h1>🎯 Bubble Shooter Pro</h1>

<div class="score">
Score: <span id="score">0</span> |
High Score: <span id="highscore"><?php echo $_SESSION['highscore']; ?></span>
</div>

<canvas id="game" width="420" height="520"></canvas>

<br><br>
<button onclick="restartGame()">Restart</button>
<button onclick="toggleSound()" id="soundBtn">🔊 Sound: ON</button>

<!-- AUDIO -->
<audio id="shootSound" src="sounds/shoot.mp3"></audio>
<audio id="popSound" src="sounds/pop.mp3"></audio>
<audio id="bgMusic" src="sounds/bg.mp3" loop></audio>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

const ROWS = 10;
const COLS = 9;
const SIZE = 22;

let grid = [];
let score = 0;
let gameOver = false;

let shooter = {x:210,y:480,angle:0};
let current = null;
let next = null;

const colors = ["#ff4d4d","#4dff4d","#4d4dff","#ffff4d","#ff4dff","#4dffff"];

// ===== SOUND SYSTEM =====
let soundEnabled = true;

const shootSound = document.getElementById("shootSound");
const popSound = document.getElementById("popSound");
const bgMusic = document.getElementById("bgMusic");

bgMusic.volume = 0.3;
shootSound.volume = 0.6;
popSound.volume = 0.6;

// enable music after first click (browser policy)
document.addEventListener("click", () => {
    if (soundEnabled && bgMusic.paused) {
        bgMusic.play().catch(()=>{});
    }
}, { once: true });

function toggleSound(){
    soundEnabled = !soundEnabled;
    const btn = document.getElementById("soundBtn");

    if(soundEnabled){
        bgMusic.play().catch(()=>{});
        btn.innerText = "🔊 Sound: ON";
    }else{
        bgMusic.pause();
        btn.innerText = "🔇 Sound: OFF";
    }
}

// ===== GAME =====
function randColor(){
    return colors[Math.floor(Math.random()*colors.length)];
}

function initGrid(){
    grid = [];
    for(let r=0;r<ROWS;r++){
        grid[r]=[];
        for(let c=0;c<COLS;c++){
            grid[r][c] = (r < 5) ? randColor() : null;
        }
    }
}

function draw(){
    ctx.clearRect(0,0,canvas.width,canvas.height);

    // grid
    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]){
                drawBubble(c*45+40, r*45+40, grid[r][c]);
            }
        }
    }

    // shooter
    ctx.strokeStyle="#fff";
    ctx.lineWidth=8;
    ctx.beginPath();
    ctx.moveTo(shooter.x,shooter.y);
    ctx.lineTo(
        shooter.x + Math.cos(shooter.angle)*50,
        shooter.y + Math.sin(shooter.angle)*50
    );
    ctx.stroke();

    // current
    if(current){
        drawBubble(current.x,current.y,current.color);
    }

    // next preview
    if(next){
        ctx.fillStyle="#fff";
        ctx.fillText("Next:",10,500);
        drawBubble(80,490,next.color);
    }
}

function drawBubble(x,y,color){
    ctx.beginPath();
    ctx.arc(x,y,SIZE,0,Math.PI*2);
    ctx.fillStyle=color;
    ctx.fill();
    ctx.strokeStyle="#fff";
    ctx.stroke();
}

// ===== SHOOT =====
function shoot(){
    if(current.speed || gameOver) return;

    if(soundEnabled){
        shootSound.currentTime = 0;
        shootSound.play();
    }

    current.speed=10;
    current.angle=shooter.angle;
}

// ===== UPDATE =====
function update(){
    if(!current || !current.speed) return;

    current.x += Math.cos(current.angle)*current.speed;
    current.y += Math.sin(current.angle)*current.speed;

    if(current.x < SIZE || current.x > canvas.width-SIZE){
        current.angle = Math.PI - current.angle;
    }

    if(current.y < 40){
        placeBubble();
    }

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]){
                let bx = c*45+40;
                let by = r*45+40;
                let dx=current.x-bx;
                let dy=current.y-by;
                if(Math.sqrt(dx*dx+dy*dy)<SIZE*2){
                    placeBubble();
                    return;
                }
            }
        }
    }
}

// ===== PLACE =====
function placeBubble(){
    let col = Math.round((current.x-40)/45);
    let row = Math.round((current.y-40)/45);

    if(!grid[row]) return;

    grid[row][col] = current.color;

    checkMatch(row,col);

    current = next;
    next = {x:210,y:480,color:randColor(),speed:0};

    if(row >= ROWS-1){
        gameOver=true;
    }
}

// ===== MATCH =====
function checkMatch(r,c){
    let color = grid[r][c];
    let stack=[[r,c]];
    let visited={};
    let match=[];

    while(stack.length){
        let [y,x]=stack.pop();
        let key=y+"_"+x;
        if(visited[key]) continue;
        visited[key]=true;

        if(grid[y] && grid[y][x]===color){
            match.push([y,x]);
            [[1,0],[-1,0],[0,1],[0,-1]].forEach(d=>{
                stack.push([y+d[0],x+d[1]]);
            });
        }
    }

    if(match.length>=3){

        if(soundEnabled){
            popSound.currentTime = 0;
            popSound.play();
        }

        match.forEach(([y,x])=>{
            grid[y][x]=null;
        });

        score += match.length*10;
        document.getElementById("score").innerText=score;

        dropFloating();
    }
}

// ===== FLOATING DROP =====
function dropFloating(){
    let visited={};

    function dfs(r,c){
        let key=r+"_"+c;
        if(visited[key] || !grid[r] || !grid[r][c]) return;
        visited[key]=true;
        [[1,0],[-1,0],[0,1],[0,-1]].forEach(d=>{
            dfs(r+d[0],c+d[1]);
        });
    }

    for(let c=0;c<COLS;c++){
        if(grid[0][c]) dfs(0,c);
    }

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c] && !visited[r+"_"+c]){
                grid[r][c]=null;
                score+=5;
            }
        }
    }
}

// ===== CONTROLS =====
canvas.addEventListener("mousemove",(e)=>{
    let rect=canvas.getBoundingClientRect();
    let mx=e.clientX-rect.left;
    let my=e.clientY-rect.top;
    shooter.angle=Math.atan2(my-shooter.y,mx-shooter.x);
});

canvas.addEventListener("click",shoot);

// ===== LOOP =====
function loop(){
    if(!gameOver){
        update();
        draw();
    }else{
        ctx.fillStyle="rgba(0,0,0,0.8)";
        ctx.fillRect(0,0,canvas.width,canvas.height);
        ctx.fillStyle="#fff";
        ctx.font="28px Arial";
        ctx.fillText("GAME OVER",120,250);
        ctx.fillText("Score: "+score,140,300);
    }
    requestAnimationFrame(loop);
}

// ===== RESTART =====
function restartGame(){
    score=0;
    gameOver=false;
    initGrid();
    current={x:210,y:480,color:randColor(),speed:0};
    next={x:210,y:480,color:randColor(),speed:0};
    document.getElementById("score").innerText=0;
}

// START
initGrid();
current={x:210,y:480,color:randColor(),speed:0};
next={x:210,y:480,color:randColor(),speed:0};
loop();

</script>
</body>
</html>
