(function () {

let isStart = false;

const tetris = {

  board: [],
  canvas: null,
  pSize: 20,
  canvasHeight: 440,
  canvasWidth: 200,
  boardHeight: 0,
  boardWidth: 0,

  spawnX: 4,
  spawnY: 1,

  shapes: [
    [[-1,1],[0,1],[1,1],[0,0]],
    [[-1,0],[0,0],[1,0],[2,0]],
    [[-1,-1],[-1,0],[0,0],[1,0]],
    [[1,-1],[-1,0],[0,0],[1,0]],
    [[0,-1],[1,-1],[-1,0],[0,0]],
    [[-1,-1],[0,-1],[0,0],[1,0]],
    [[0,-1],[1,-1],[0,0],[1,0]]
  ],

  curShape: null,
  curShapeIndex: null,
  curX: 0,
  curY: 0,
  curSqs: [],
  sqs: [],

  score: 0,
  level: 1,
  lines: 0,
  time: 0,

  speed: 700,
  timer: null,
  gameLoopTimer: null,

  init() {
    isStart = true;
    this.canvas = document.getElementById("canvas");

    this.initBoard();
    this.bindKeys();
    this.spawnShape();
    this.startGameLoop();
  },

  initBoard() {
    this.boardHeight = this.canvasHeight / this.pSize;
    this.boardWidth = this.canvasWidth / this.pSize;

    this.board = new Array(this.boardHeight * this.boardWidth).fill(0);
  },

  spawnShape() {
    this.curShapeIndex = Math.floor(Math.random() * this.shapes.length);
    this.curShape = this.shapes[this.curShapeIndex];

    this.curX = this.spawnX;
    this.curY = this.spawnY;

    this.drawShape();
  },

  drawShape() {
    this.curSqs = [];

    this.curShape.forEach(p => {
      const el = document.createElement("div");
      el.className = "square type" + this.curShapeIndex;

      const x = (p[0] + this.curX) * this.pSize;
      const y = (p[1] + this.curY) * this.pSize;

      el.style.left = x + "px";
      el.style.top = y + "px";

      this.canvas.appendChild(el);
      this.curSqs.push(el);
    });
  },

  clearShape() {
    this.curSqs.forEach(el => this.canvas.removeChild(el));
    this.curSqs = [];
  },

  move(dx, dy) {
    this.clearShape();
    this.curX += dx;
    this.curY += dy;
    this.drawShape();
  },

  rotate() {
    if (this.curShapeIndex === 6) return;

    const rotated = this.curShape.map(([x, y]) => [-y, x]);
    this.clearShape();
    this.curShape = rotated;
    this.drawShape();
  },

  bindKeys() {
    document.addEventListener("keydown", (e) => {
      switch (e.keyCode) {
        case 37: this.move(-1, 0); break;
        case 39: this.move(1, 0); break;
        case 40: this.move(0, 1); break;
        case 38: this.rotate(); break;
      }
    });
  },

  startGameLoop() {
    this.gameLoopTimer = setInterval(() => {
      this.move(0, 1);
    }, this.speed);
  }

};

// START BUTTON
document.getElementById("start").addEventListener("click", () => {
  if (!isStart) {
    document.getElementById("start").style.display = "none";
    tetris.init();
  }
});

})();
