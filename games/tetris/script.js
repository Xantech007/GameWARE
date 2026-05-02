const canvas = document.getElementById("board");
const ctx = canvas.getContext("2d");

canvas.width = 240;
canvas.height = 400;

const COLS = 12;
const ROWS = 20;
const SIZE = 20;

let board = Array.from({length: ROWS}, () => Array(COLS).fill(0));

const colors = ["#000","#f00","#0f0","#00f","#ff0","#0ff","#f0f"];

const shapes = [
    [[1,1,1,1]], // I
    [[1,1],[1,1]], // O
    [[0,1,0],[1,1,1]], // T
    [[1,0,0],[1,1,1]], // L
    [[0,0,1],[1,1,1]] // J
];

let piece = null;
let score = 0;
let running = false;

function newPiece(){
    let shape = shapes[Math.floor(Math.random()*shapes.length)];
    return {
        shape,
        x: 4,
        y: 0
    };
}

function draw(){
    ctx.clearRect(0,0,canvas.width,canvas.height);

    // draw board
    board.forEach((row,y)=>{
        row.forEach((val,x)=>{
            if(val){
                ctx.fillStyle = colors[val];
                ctx.fillRect(x*SIZE,y*SIZE,SIZE,SIZE);
            }
        });
    });

    // draw piece
    piece.shape.forEach((row,y)=>{
        row.forEach((val,x)=>{
            if(val){
                ctx.fillStyle = "#0ff";
                ctx.fillRect((piece.x+x)*SIZE,(piece.y+y)*SIZE,SIZE,SIZE);
            }
        });
    });
}

function collide(){
    return piece.shape.some((row,y)=>{
        return row.some((val,x)=>{
            let px = piece.x + x;
            let py = piece.y + y;
            return val && (
                px < 0 ||
                px >= COLS ||
                py >= ROWS ||
                (py >= 0 && board[py][px])
            );
        });
    });
}

function merge(){
    piece.shape.forEach((row,y)=>{
        row.forEach((val,x)=>{
            if(val){
                board[piece.y+y][piece.x+x] = 1;
            }
        });
    });
}

function rotatePiece(){
    const rotated = piece.shape[0].map((_,i)=>
        piece.shape.map(row=>row[i]).reverse()
    );
    piece.shape = rotated;
}

function clearLines(){
    board = board.filter(row=>row.some(val=>!val));
    while(board.length < ROWS){
        board.unshift(Array(COLS).fill(0));
        score += 10;
    }
    document.getElementById("score").innerText = score;
}

function drop(){
    piece.y++;
    if(collide()){
        piece.y--;
        merge();
        clearLines();
        piece = newPiece();

        if(collide()){
            alert("Game Over");
            running = false;
        }
    }
}

function move(dir){
    piece.x += dir;
    if(collide()) piece.x -= dir;
}

function rotate(){
    let prev = piece.shape;
    rotatePiece();
    if(collide()) piece.shape = prev;
}

function gameLoop(){
    if(!running) return;
    drop();
    draw();
    setTimeout(gameLoop, 500);
}

document.getElementById("start").onclick = () => {
    board = Array.from({length: ROWS}, () => Array(COLS).fill(0));
    piece = newPiece();
    score = 0;
    running = true;
    gameLoop();
};

document.addEventListener("keydown", e=>{
    if(!running) return;

    if(e.key === "ArrowLeft") move(-1);
    if(e.key === "ArrowRight") move(1);
    if(e.key === "ArrowDown") drop();
    if(e.key === "ArrowUp") rotate();
});
