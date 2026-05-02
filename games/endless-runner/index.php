<?php
// endless-runner.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Endless Runner</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f0f1e;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            touch-action: none;
        }
        canvas {
            image-rendering: pixelated;
            border: 2px solid #333;
            max-width: 100%;
            height: auto;
        }
        #ui {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 1.2rem;
            z-index: 10;
        }
        #start-screen, #game-over {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9);
            padding: 30px 50px;
            border-radius: 15px;
            text-align: center;
            z-index: 20;
        }
        button {
            margin-top: 15px;
            padding: 12px 30px;
            font-size: 1.1rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div id="ui">
        Score: <span id="score">0</span>m
    </div>

    <canvas id="game" width="800" height="400"></canvas>

    <div id="start-screen">
        <h1>🏃 Endless Runner</h1>
        <p>Tap / Space to Jump</p>
        <button onclick="startGame()">START GAME</button>
    </div>

    <div id="game-over" style="display:none">
        <h1>Game Over</h1>
        <p>Your Score: <span id="final-score">0</span>m</p>
        <button onclick="restartGame()">PLAY AGAIN</button>
    </div>

    <script src="js/endless-runner.js"></script>
</body>
</html>
