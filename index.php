<?php
session_start();
if (!isset($_SESSION['highscore'])) $_SESSION['highscore'] = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bubble Shooter Pro Ultimate+</title>

<style>
body{
    margin:0;
    background: radial-gradient(circle,#050505,#000);
    font-family:Arial;
    text-align:center;
    color:#fff;
}

h1{margin:10px;font-size:20px;}

canvas{
    background:#000;
    border:3px solid #333;
    border-radius:12px;
    box-shadow:0 0 30px #0ff;
    touch-action:none;
    width:100%;
    max-width:500px;
}

button{
    padding:10px 15px;
    margin:5px;
    border:none;
    border-radius:6px;
    background:#00c3ff;
    color:#000;
    font-weight:bold;
}
</style>
</head>

<body>

<h1>🎯 Bubble Shooter Pro+</h1>

<div>
Score: <span id="score">0</span> |
High Score: <span><?php echo $_SESSION['highscore']; ?></span>
</div>

<canvas id="game"></canvas>

<br>
<button onclick="restartGame()">Restart</button>
<button onclick="toggleSound()" id="soundBtn">🔊 Sound</button>

<audio id="shootSound" src="sounds/shoot.mp3"></audio>
<audio id="popSound" src="sounds/pop.mp3"></audio>
<audio id="bgMusic" src="sounds/bg.mp3" loop></audio>

<script>
const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

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
    if(sound){ bg.play().catch(()=>{}); soundBtn.innerText="🔊 Sound";}
    else{ bg.pause(); soundBtn.innerText="🔇 Mute";}
}

// ===== GAME =====
const SIZE=22, ROWS=10, COLS=9;
let grid=[],score=0,gameOver=false;
let shooter={x:210,y:480,angle:0};
let current,next;
let colors=["#ff4d4d","#4dff4d","#4d4dff","#ffff4d","#ff4dff","#4dffff"];

// ===== FX =====
let effects=[];
let stars=[];

for(let i=0;i<60;i++){
    stars.push({x:Math.random()*BASE_W,y:Math.random()*BASE_H,s:Math.random()*2});
}

// ===== UTILS =====
function rand(){return colors[Math.floor(Math.random()*colors.length)];}

// ===== INIT =====
function init(){
    grid=[];
    for(let r=0;r<ROWS;r++){
        grid[r]=[];
        for(let c=0;c<COLS;c++){
            grid[r][c]=(r<5)?rand():null;
        }
    }
}

// ===== DRAW BG =====
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

// ===== BUBBLE =====
function drawBubble(x,y,color,scale=1,alpha=1){
    ctx.save();
    ctx.globalAlpha=alpha;
    ctx.shadowColor=color;
    ctx.shadowBlur=15;

    let r=SIZE*scale;

    let g=ctx.createRadialGradient(x-r*0.4,y-r*0.4,r*0.2,x,y,r);
    g.addColorStop(0,"#fff");
    g.addColorStop(0.3,color);
    g.addColorStop(1,"#000");

    ctx.beginPath();
    ctx.arc(x,y,r,0,Math.PI*2);
    ctx.fillStyle=g;
    ctx.fill();

    ctx.beginPath();
    ctx.arc(x-r*0.3,y-r*0.3,r*0.3,0,Math.PI*2);
    ctx.fillStyle="rgba(255,255,255,0.4)";
    ctx.fill();

    ctx.restore();
}

// ===== DRAW =====
function draw(){
    drawBG();

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]){
                drawBubble(c*45+40,r*45+40,grid[r][c]);
            }
        }
    }

    // AIM LINE
    ctx.setLineDash([5,5]);
    ctx.strokeStyle="rgba(255,255,255,0.4)";
    ctx.beginPath();
    ctx.moveTo(shooter.x,shooter.y);
    ctx.lineTo(
        shooter.x+Math.cos(shooter.angle)*300,
        shooter.y+Math.sin(shooter.angle)*300
    );
    ctx.stroke();
    ctx.setLineDash([]);

    // SHOOTER
    ctx.shadowColor="#0ff";
    ctx.shadowBlur=10;
    ctx.strokeStyle="#0ff";
    ctx.lineWidth=10;
    ctx.beginPath();
    ctx.moveTo(shooter.x,shooter.y);
    ctx.lineTo(
        shooter.x+Math.cos(shooter.angle)*50,
        shooter.y+Math.sin(shooter.angle)*50
    );
    ctx.stroke();
    ctx.shadowBlur=0;

    if(current) drawBubble(current.x,current.y,current.color);

    if(next){
        ctx.fillText("Next:",10,500);
        drawBubble(80,490,next.color);
    }

    drawFX();
}

// ===== FX =====
function drawFX(){
    for(let i=effects.length-1;i>=0;i--){
        let e=effects[i];
        drawBubble(e.x,e.y,"#fff",1+(20-e.life)/10,e.life/20);
        e.life--;
        if(e.life<=0) effects.splice(i,1);
    }
}

// ===== SHOOT =====
function shoot(){
    if(current.speed||gameOver)return;
    if(sound){shootS.currentTime=0;shootS.play();}
    current.speed=10;
    current.angle=shooter.angle;
}

// ===== UPDATE =====
function update(){
    if(!current||!current.speed)return;

    current.x+=Math.cos(current.angle)*current.speed;
    current.y+=Math.sin(current.angle)*current.speed;

    if(current.x<SIZE||current.x>canvas.width-SIZE){
        current.angle=Math.PI-current.angle;
    }

    if(current.y<40) place();

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]){
                let dx=current.x-(c*45+40);
                let dy=current.y-(r*45+40);
                if(Math.sqrt(dx*dx+dy*dy)<SIZE*2){
                    place();return;
                }
            }
        }
    }
}

// ===== PLACE =====
function place(){
    let col=Math.round((current.x-40)/45);
    let row=Math.round((current.y-40)/45);

    if(!grid[row])return;

    grid[row][col]=current.color;

    match(row,col);

    current=next;
    next={x:210,y:480,color:rand(),speed:0};

    if(row>=ROWS-1) gameOver=true;
}

// ===== MATCH =====
function match(r,c){
    let col=grid[r][c];
    let stack=[[r,c]],seen={},m=[];

    while(stack.length){
        let [y,x]=stack.pop();
        let k=y+"_"+x;
        if(seen[k])continue;
        seen[k]=1;

        if(grid[y]&&grid[y][x]==col){
            m.push([y,x]);
            [[1,0],[-1,0],[0,1],[0,-1]].forEach(d=>stack.push([y+d[0],x+d[1]]));
        }
    }

    if(m.length>=3){
        if(sound){popS.currentTime=0;popS.play();}

        m.forEach(([y,x])=>{
            effects.push({x:x*45+40,y:y*45+40,life:20});
            grid[y][x]=null;
        });

        score+=m.length*10;
        scoreEl.innerText=score;
        drop();
    }
}

// ===== DROP =====
function drop(){
    let vis={};

    function dfs(r,c){
        let k=r+"_"+c;
        if(vis[k]||!grid[r]||!grid[r][c])return;
        vis[k]=1;
        [[1,0],[-1,0],[0,1],[0,-1]].forEach(d=>dfs(r+d[0],c+d[1]));
    }

    for(let c=0;c<COLS;c++) if(grid[0][c]) dfs(0,c);

    for(let r=0;r<ROWS;r++){
        for(let c=0;c<COLS;c++){
            if(grid[r][c]&&!vis[r+"_"+c]){
                grid[r][c]=null;
                score+=5;
            }
        }
    }
}

// ===== INPUT (SCALED) =====
function pos(e){
    let r=canvas.getBoundingClientRect();
    return {
        x:(e.clientX-r.left)*(canvas.width/r.width),
        y:(e.clientY-r.top)*(canvas.height/r.height)
    };
}

// DESKTOP
canvas.addEventListener("mousemove",e=>{
    let p=pos(e);
    shooter.angle=Math.atan2(p.y-shooter.y,p.x-shooter.x);
});
canvas.addEventListener("click",shoot);

// MOBILE
let t=false;
canvas.addEventListener("touchstart",e=>{
    e.preventDefault();t=true;
    let p=pos(e.touches[0]);
    shooter.angle=Math.atan2(p.y-shooter.y,p.x-shooter.x);
},{passive:false});

canvas.addEventListener("touchmove",e=>{
    e.preventDefault();if(!t)return;
    let p=pos(e.touches[0]);
    shooter.angle=Math.atan2(p.y-shooter.y,p.x-shooter.x);
},{passive:false});

canvas.addEventListener("touchend",e=>{
    e.preventDefault();t=false;shoot();
},{passive:false});

// ===== LOOP =====
function loop(){
    if(!gameOver){
        update();draw();
    }else{
        ctx.fillStyle="rgba(0,0,0,0.8)";
        ctx.fillRect(0,0,canvas.width,canvas.height);
        ctx.fillStyle="#fff";
        ctx.font="26px Arial";
        ctx.fillText("GAME OVER",120,250);
        ctx.fillText("Score: "+score,140,300);
    }
    requestAnimationFrame(loop);
}

// ===== START =====
init();
current={x:210,y:480,color:rand(),speed:0};
next={x:210,y:480,color:rand(),speed:0};
loop();
</script>

</body>
</html>
