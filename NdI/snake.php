<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Snake avec conditions de déblocage multiples</title>
<style>
    canvas{
        border: 2px solid #333;
        display: block;
        margin: 0 auto;
        background-color: #f0f0f0;
    }
    body {
        margin: 0;
        background-color: #f0f0f0;
    }
    #message-deblocage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 20px;
        color: #fff;
        background: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        z-index: 100;
        line-height: 1.5;
        font-family: Arial, sans-serif;
    }
</style>
</head>
<body>
<canvas width="600" height="600"></canvas>
<div id="message-deblocage" style="display:none;"></div> 

<script>
// --- PARAMÈTRES DE DÉBLOCAGE ---
const CLE_DEBLOCAGE = 'jeu_precedent_termine';
const VALEUR_EXACTE_DEBLOCAGE = 13; 
// Clés des scores secondaires
const CLE_SCORE_2 = 'score_jeu_2';
const CLE_SCORE_3 = 'score_jeu_3';
const CLE_SCORE_4 = 'score_jeu_4';
// ------------------------------

const canvas = document.querySelector("canvas");
const ctx = canvas.getContext('2d');
const messageDeblocage = document.getElementById('message-deblocage');

let box = 30;
let snake = [{ x: 10*box, y: 10*box }];
let food = {
    x: Math.floor(Math.random() * 15 + 1)*box,
    y: Math.floor(Math.random() * 15 + 1)*box
};

let score = 0;
let d;
let headScale = 1;
let borderWidth = 2;

let speed = 150;
let gameOver = false;
let game; // Variable pour l'intervalle du jeu

const loseSound = new Audio('lose.mp3');
const background = new Image();
background.src = 'pinguin.webp';

// --- LOGIQUE DE VÉRIFICATION ET D'AFFICHAGE ---

function verifierDeblocage() {
    // 1. Récupération des scores
    const scorePrincipal = parseInt(localStorage.getItem(CLE_DEBLOCAGE)) || 0;
    const score2 = parseInt(localStorage.getItem(CLE_SCORE_2)) || 0;
    const score3 = parseInt(localStorage.getItem(CLE_SCORE_3)) || 0;
    const score4 = parseInt(localStorage.getItem(CLE_SCORE_4)) || 0;

    // 2. Évaluation des conditions
    const condition1 = scorePrincipal === VALEUR_EXACTE_DEBLOCAGE;
    const condition2_3_4 = score2 > 0 && score3 > 0 && score4 > 0;

    const deblocageTotal = condition1 && condition2_3_4;
    
    // 3. Gestion de l'affichage (Verrouillé ou Débloqué)
    if (deblocageTotal) {
        // Débloqué : On cache le message et on retire les filtres
        messageDeblocage.style.display = 'none';
        canvas.style.filter = 'none';
        canvas.style.opacity = 1;
        return true;
    } else {
        // Verrouillé : On affiche le message et on applique les filtres
        messageDeblocage.style.display = 'block';
        canvas.style.filter = 'blur(5px) grayscale(100%)';
        canvas.style.opacity = 0.5;

        // Mise à jour du message pour guider l'utilisateur
        let messageHTML = "🔓 **Jeu Verrouillé !** 🔒<br>Vous devez remplir les conditions suivantes :<hr>";
        
        if (!condition1) {
            messageHTML += `❌ **Score Principal (${CLE_DEBLOCAGE}) :** Doit être **EXACTEMENT ${VALEUR_EXACTE_DEBLOCAGE}** (Actuel: ${scorePrincipal})<br>`;
        } else {
             messageHTML += `✅ **Score Principal :** OK<br>`;
        }

        // Afficher l'état de chaque score secondaire
        const checkScore = (key, score) => score > 0 ? `✅ **Score ${key} :** OK<br>` : `❌ **Score ${key} :** Doit être > 0 (Actuel: ${score})<br>`;
        
        messageHTML += checkScore(CLE_SCORE_2, score2);
        messageHTML += checkScore(CLE_SCORE_3, score3);
        messageHTML += checkScore(CLE_SCORE_4, score4);
        
        messageDeblocage.innerHTML = messageHTML;
        return false;
    }
}


document.addEventListener("keydown", direction);

function direction(event){
    // Empêcher le mouvement si le jeu n'est pas lancé
    if (typeof game === 'undefined' || gameOver || !verifierDeblocage()) return;
    
    let key = event.keyCode;
    if (key == 37 && d != "RIGHT") d = "LEFT";
    else if (key == 38 && d != "DOWN") d = "UP";
    else if (key == 39 && d != "LEFT") d = "RIGHT";
    else if (key == 40 && d != "UP") d = "DOWN";
}

// Dessiner rectangle arrondi (inchangé)
function roundRect(x, y, w, h, r, fillColor, strokeColor, lineWidth) {
    ctx.beginPath();
    ctx.moveTo(x+r, y);
    ctx.lineTo(x+w-r, y);
    ctx.quadraticCurveTo(x+w, y, x+w, y+r);
    ctx.lineTo(x+w, y+h-r);
    ctx.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
    ctx.lineTo(x+r, y+h);
    ctx.quadraticCurveTo(x, y+h, x, y+h-r);
    ctx.lineTo(x, y+r);
    ctx.quadraticCurveTo(x, y, x+r, y);
    ctx.closePath();
    ctx.fillStyle = fillColor;
    ctx.fill();
    ctx.lineWidth = lineWidth;
    ctx.strokeStyle = strokeColor;
    ctx.stroke();
}

function draw(){
    // Si le jeu n'est pas lancé, on dessine juste le fond flouté
    if (typeof game === 'undefined') {
        ctx.drawImage(background, 0, 0, canvas.width, canvas.height);
        return; 
    }
    
    // Dessiner l'image de fond
    ctx.drawImage(background, 0, 0, canvas.width, canvas.height);

    if(gameOver){
        // Afficher "PERDU"
        ctx.fillStyle = "rgba(0,0,0,0.6)";
        ctx.fillRect(0, canvas.height/2 - 50, canvas.width, 100);
        ctx.fillStyle = "red";
        ctx.font = "60px Arial";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText("PERDU", canvas.width/2, canvas.height/2);
        
        clearInterval(game);
        return;
    }
    // ... Reste de la logique de jeu ... (Mouvement, nourriture, dessin du serpent, score, accélération)

    let snakeX = snake[0].x;
    let snakeY = snake[0].y;

    if(d == "LEFT") snakeX -= box;
    if(d == "UP") snakeY -= box;
    if(d == "RIGHT") snakeX += box;
    if(d == "DOWN") snakeY += box;

    let ateFood = false;

    // Manger la nourriture
    if(snakeX == food.x && snakeY == food.y){
        score++;
        headScale = 1.5;
        borderWidth = 6;
        ateFood = true;
        food = {
            x: Math.floor(Math.random() * 15 + 1)*box,
            y: Math.floor(Math.random() * 15 + 1)*box
        };
    } else {
        snake.pop();
    }

    let newHead = { x: snakeX, y: snakeY };

    if(snakeX < 0 || snakeY < 0 || snakeX >= 20*box || snakeY >= 20*box || collision(newHead, snake)){
        gameOver = true;
        loseSound.play();
        return;
    }

    snake.unshift(newHead);

    // Dessiner la nourriture
    ctx.fillStyle = "orange";
    ctx.beginPath();
    ctx.arc(food.x + box/2, food.y + box/2, box/2, 0, Math.PI*2);
    ctx.fill();

    // Dessiner le serpent
    for(let i=0; i<snake.length; i++){
        if(i==0){
            let size = box * headScale;
            let grad = ctx.createRadialGradient(snake[i].x+box/2, snake[i].y+box/2, 2, snake[i].x+box/2, snake[i].y+box/2, box);
            grad.addColorStop(0, "#00ff00");
            grad.addColorStop(1, "#006600");

            ctx.shadowColor = "#00ff00";
            ctx.shadowBlur = 10;

            roundRect(snake[i].x - (size-box)/2, snake[i].y - (size-box)/2, size, size, 6, grad, "red", borderWidth);

            headScale -= 0.1;
            if(headScale<1) headScale=1;
            borderWidth -= 0.8;
            if(borderWidth<2) borderWidth=2;

            ctx.shadowBlur = 0;
        } else {
            let greenValue = 150 + Math.floor((i/snake.length)*105);
            let fillColor = `rgb(0, ${greenValue}, 0)`;
            roundRect(snake[i].x, snake[i].y, box, box, 4, fillColor, "red", 2);
        }
    }

    // Afficher le score
    let scoreX = 15;
    let scoreY = 25;
    let padding = 8;
    ctx.font = "24px Arial";
    ctx.textBaseline = "middle";
    ctx.textAlign = "left";

    let text = score.toString();
    let textWidth = ctx.measureText(text).width;

    ctx.fillStyle = "rgba(128,0,128,0.5)";
    ctx.beginPath();
    ctx.moveTo(scoreX - padding, scoreY - 20);
    ctx.lineTo(scoreX + textWidth + padding, scoreY - 20);
    ctx.lineTo(scoreX + textWidth + padding, scoreY + 10);
    ctx.lineTo(scoreX - padding, scoreY + 10);
    ctx.closePath();
    ctx.fill();

    ctx.fillStyle = "white";
    ctx.shadowColor = "black";
    ctx.shadowBlur = 2;
    ctx.fillText(text, scoreX, scoreY - 5);
    ctx.shadowBlur = 0;

    // Accélération
    if(ateFood && speed > 30){
        speed -= 2;
        clearInterval(game);
        game = setInterval(draw, speed);
    }
}

function collision(head, array){
    return array.some(cell => head.x == cell.x && head.y == cell.y);
}

// --- INITIALISATION AU CHARGEMENT ---
window.onload = () => {
    // Vérifie et met à jour l'affichage (message de déblocage)
    const estDebloque = verifierDeblocage();

    // Lancement de la boucle de jeu UNIQUEMENT si débloqué
    if (estDebloque) {
        game = setInterval(draw, speed);
    } 

    // Appel initial pour afficher le fond (même si flouté)
    background.onload = () => {
        draw(); 
    };
}
</script>
</body>
</html>