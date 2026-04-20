<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mini Race Car Pro</title>

<style>
body{margin:0;background:#000;font-family:Arial;text-align:center;color:#fff;}
canvas{background:#111;border:3px solid #333;border-radius:10px;width:100%;max-width:400px;touch-action:none;}
button{padding:10px;margin:10px;border:none;border-radius:5px;background:#00c3ff;font-weight:bold;}
.popup{
    position:fixed;
    top:40%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#111;
    padding:20px;
    border:2px solid #00c3ff;
    display:none;
}
</style>
</head>

<body>

<h2>🏎️ Mini Race Car Pro</h2>
Score: <span id="score">0</span>

<br>
<canvas id="game" width="400" height="600"></canvas>
<br>
<button onclick="restartGame()">Restart</button>

<div id="popup" class="popup"></div>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

let score=0,speed=4,gameOver=false,shake=0,nitro=false;
let submitted=false;

// ⏱️ TRACK TIME
let startTime = Date.now();

let car={x:180,y:500,w:40,h:70};
let keys={},obstacles=[];
let roadY=0,bgY=0;

// ===== SAFE IMAGE LOADER =====
function loadImg(src){
    let img=new Image();
    img.src=src;
    img.loaded=false;
    img.failed=false;
    img.onload=()=>img.loaded=true;
    img.onerror=()=>img.failed=true;
    return img;
}

const playerImg = loadImg("../assets/images/player.png");
const enemyImg  = loadImg("../assets/images/enemy.png");
const truckImg  = loadImg("../assets/images/truck.png");

// ===== BACKGROUND =====
function drawBackground(){
    bgY += speed*0.3;
    if(bgY>600) bgY=0;

    ctx.fillStyle="#050505";
    ctx.fillRect(0,0,400,600);

    ctx.fillStyle="#222";
    for(let i=0;i<10;i++){
        let x=i*40;
        let h=Math.sin(i+bgY*0.01)*50+100;
        ctx.fillRect(x,600-h,30,h);
    }
}

// ===== ROAD =====
function drawRoad(){
    ctx.fillStyle="#333";
    ctx.fillRect(100,0,200,600);

    ctx.strokeStyle="#fff";
    ctx.lineWidth=4;

    for(let i=0;i<600;i+=40){
        ctx.beginPath();
        ctx.moveTo(200,i+roadY);
        ctx.lineTo(200,i+20+roadY);
        ctx.stroke();
    }

    roadY+=speed;
    if(roadY>40) roadY=0;
}

// ===== PLAYER =====
function drawPlayer(){
    if(playerImg.loaded){
        ctx.drawImage(playerImg,car.x,car.y,car.w,car.h);
    }else{
        ctx.fillStyle="#00ffcc";
        ctx.fillRect(car.x,car.y,car.w,car.h);
    }
}

// ===== SPAWN =====
let lastSpawnTime=0;

function spawnObstacle(){
    let now=Date.now();
    if(now-lastSpawnTime<800) return;

    let lanes=[0,1,2];

    obstacles.forEach(o=>{
        if(o.y<200){
            let lane=Math.floor((o.x-120)/60);
            lanes=lanes.filter(l=>l!==lane);
        }
    });

    if(lanes.length<=1) return;

    let lane=lanes[Math.floor(Math.random()*lanes.length)];
    let x=120+lane*60;
    let isTruck=Math.random()<0.3;

    obstacles.push({
        x:x,
        y:-100,
        w:40,
        h:isTruck?110:70,
        type:isTruck?"truck":"car"
    });

    lastSpawnTime=now;
}

// ===== OBSTACLES =====
function drawObstacles(){
    for(let o of obstacles){
        o.y+=speed;

        if(o.type==="truck" && truckImg.loaded){
            ctx.drawImage(truckImg,o.x,o.y,o.w,o.h);
        }else if(o.type==="car" && enemyImg.loaded){
            ctx.drawImage(enemyImg,o.x,o.y,o.w,o.h);
        }else{
            ctx.fillStyle=o.type==="truck"?"#ffaa00":"#ff4444";
            ctx.fillRect(o.x,o.y,o.w,o.h);
        }

        if(
            car.x < o.x + o.w &&
            car.x + car.w > o.x &&
            car.y < o.y + o.h &&
            car.y + car.h > o.y
        ){
            gameOver=true;
            shake=20;
        }
    }

    obstacles=obstacles.filter(o=>o.y<700);
}

// ===== UPDATE =====
function update(){
    if(gameOver) return;

    if(keys["ArrowLeft"]) car.x-=6;
    if(keys["ArrowRight"]) car.x+=6;

    speed = nitro ? 8 : speed+0.002;

    car.x=Math.max(110,Math.min(250,car.x));

    if(Math.random()<0.03) spawnObstacle();

    score++;
    document.getElementById("score").innerText=score;
}

// ===== SEND EARNINGS =====
function sendEarnings(){
    if(submitted) return;
    submitted = true;

    let duration = Math.floor((Date.now() - startTime)/1000);

    // 🚫 anti-cheat: ignore very short runs
    if(duration < 3) return;

    fetch("../api/earn.php",{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"duration="+duration+"&score="+score
    })
    .then(res=>res.json())
    .then(data=>{
        let popup=document.getElementById("popup");
        popup.style.display="block";

        if(data.status==="credited"){
            popup.innerHTML = "💰 Earned: "+data.currency+" "+parseFloat(data.amount).toFixed(2);
        }else{
            popup.innerHTML = "👤 Guest: "+data.currency+" "+parseFloat(data.amount).toFixed(2)+"<br>Login to claim!";
        }
    });
}

// ===== DRAW =====
function draw(){
    ctx.save();

    if(shake>0){
        ctx.translate(Math.random()*shake-shake/2,Math.random()*shake-shake/2);
        shake--;
    }

    ctx.clearRect(0,0,400,600);

    drawBackground();
    drawRoad();
    drawPlayer();
    drawObstacles();

    if(nitro){
        ctx.fillStyle="rgba(0,255,255,0.2)";
        ctx.fillRect(0,0,400,600);
    }

    if(gameOver){
        sendEarnings();

        ctx.fillStyle="rgba(0,0,0,0.7)";
        ctx.fillRect(0,0,400,600);

        ctx.fillStyle="#fff";
        ctx.font="28px Arial";
        ctx.fillText("CRASH!",130,280);
    }

    ctx.restore();
}

// ===== LOOP =====
function loop(){
    update();
    draw();
    requestAnimationFrame(loop);
}

// ===== CONTROLS =====
document.addEventListener("keydown",e=>{
    keys[e.key]=true;
    if(e.key===" ") nitro=true;
});
document.addEventListener("keyup",e=>{
    keys[e.key]=false;
    if(e.key===" ") nitro=false;
});

// MOBILE
canvas.addEventListener("touchmove",e=>{
    e.preventDefault();
    let rect=canvas.getBoundingClientRect();
    let x=e.touches[0].clientX-rect.left;
    car.x=x-20;
},{passive:false});

canvas.addEventListener("touchstart",()=>nitro=true);
canvas.addEventListener("touchend",()=>nitro=false);

// ===== RESTART =====
function restartGame(){
    score=0;
    speed=4;
    gameOver=false;
    submitted=false;
    startTime = Date.now();
    car.x=180;
    obstacles=[];
    document.getElementById("popup").style.display="none";
}

// START
loop();
</script>

</body>
</html>
