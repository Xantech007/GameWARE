<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mini Race Car</title>

<style>
body{
    margin:0;
    background:#111;
    font-family:Arial;
    text-align:center;
    color:#fff;
}

h2{margin:10px;}

canvas{
    background:#222;
    border:3px solid #444;
    border-radius:10px;
    width:100%;
    max-width:400px;
    touch-action:none;
}

button{
    padding:10px 15px;
    margin:10px;
    border:none;
    border-radius:5px;
    background:#00c3ff;
    color:#000;
    font-weight:bold;
}
</style>
</head>

<body>

<h2>🏎️ Mini Race Car</h2>
Score: <span id="score">0</span>

<br>
<canvas id="game" width="400" height="600"></canvas>
<br>
<button onclick="restartGame()">Restart</button>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

let score = 0;
let speed = 4;
let gameOver = false;

let car = {
    x:180,
    y:500,
    w:40,
    h:70
};

let keys = {};
let obstacles = [];

// ===== DRAW CAR =====
function drawCar(x,y,color){
    ctx.fillStyle=color;
    ctx.fillRect(x,y,40,70);

    // windows
    ctx.fillStyle="#000";
    ctx.fillRect(x+5,y+10,30,20);
}

// ===== DRAW ROAD =====
let roadY = 0;

function drawRoad(){
    ctx.fillStyle="#333";
    ctx.fillRect(100,0,200,600);

    ctx.strokeStyle="#fff";
    ctx.lineWidth=5;

    for(let i=0;i<600;i+=40){
        ctx.beginPath();
        ctx.moveTo(200, i+roadY);
        ctx.lineTo(200, i+20+roadY);
        ctx.stroke();
    }

    roadY += speed;
    if(roadY > 40) roadY = 0;
}

// ===== SPAWN OBSTACLES =====
function spawnObstacle(){
    let lane = Math.floor(Math.random()*3);
    let x = 120 + lane*60;

    obstacles.push({
        x:x,
        y:-80,
        w:40,
        h:70
    });
}

// ===== DRAW OBSTACLES =====
function drawObstacles(){
    ctx.fillStyle="#ff4444";

    for(let i=0;i<obstacles.length;i++){
        let o = obstacles[i];
        o.y += speed;

        ctx.fillRect(o.x,o.y,o.w,o.h);

        // collision
        if(
            car.x < o.x + o.w &&
            car.x + car.w > o.x &&
            car.y < o.y + o.h &&
            car.y + car.h > o.y
        ){
            gameOver = true;
        }
    }

    // remove off screen
    obstacles = obstacles.filter(o => o.y < 700);
}

// ===== UPDATE =====
function update(){
    if(gameOver) return;

    if(keys["ArrowLeft"]) car.x -= 6;
    if(keys["ArrowRight"]) car.x += 6;

    // boundaries
    car.x = Math.max(110, Math.min(250, car.x));

    if(Math.random() < 0.02){
        spawnObstacle();
    }

    score++;
    speed += 0.0005;

    document.getElementById("score").innerText = score;
}

// ===== DRAW =====
function draw(){
    ctx.clearRect(0,0,400,600);

    drawRoad();
    drawCar(car.x,car.y,"#00ffcc");
    drawObstacles();

    if(gameOver){
        ctx.fillStyle="rgba(0,0,0,0.7)";
        ctx.fillRect(0,0,400,600);

        ctx.fillStyle="#fff";
        ctx.font="28px Arial";
        ctx.fillText("GAME OVER",110,300);
    }
}

// ===== LOOP =====
function loop(){
    update();
    draw();
    requestAnimationFrame(loop);
}

// ===== CONTROLS =====
document.addEventListener("keydown",e=>{
    keys[e.key] = true;
});
document.addEventListener("keyup",e=>{
    keys[e.key] = false;
});

// MOBILE TOUCH
canvas.addEventListener("touchmove",e=>{
    e.preventDefault();
    let rect = canvas.getBoundingClientRect();
    let touch = e.touches[0];
    let x = touch.clientX - rect.left;

    car.x = x - 20;
},{passive:false});

// ===== RESTART =====
function restartGame(){
    score = 0;
    speed = 4;
    gameOver = false;
    car.x = 180;
    obstacles = [];
}

// START
loop();
</script>

</body>
</html>
