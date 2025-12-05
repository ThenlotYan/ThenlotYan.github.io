<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Snake</title>
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
        font-size: 20px; /* Taille légèrement réduite pour plus de lignes */
        color: #fff;
        background: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        z-index: 100;
        line-height: 1.5;
        font-family: Arial, sans-serif;
    }
    #bouton-deverrouillage {
        display: none; /* Caché par défaut */
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150px;
        height: 150px;
        border: 4px solid gold;
        border-radius: 50%;
        cursor: pointer;
        z-index: 101;
        box-shadow: 0 0 15px gold;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
        100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
</style>
</head>
<body>
<canvas width="600" height="600"></canvas>
<div id="message-deblocage"></div> 
<img id="bouton-deverrouillage" src="bouton_cle.png" alt="Cliquer pour déverrouiller le Snake"> 

<script>
// --- PARAMÈTRES DE DÉBLOCAGE ---
const CLE_DEBLOCAGE = 'jeu_precedent_termine';
const VALEUR_EXACTE_DEBLOCAGE = 13; 
// Nouvelles clés à vérifier
const CLE_SCORE_2 = 'score_jeu_2';
const CLE_SCORE_3 = 'score_jeu_3';
const CLE_SCORE_4 = 'score_jeu_4';
// ------------------------------

const canvas = document.querySelector("canvas");
const ctx = canvas.getContext('2d');
const messageDeblocage = document.getElementById('message-deblocage');
const boutonDeverrouillage = document.getElementById('bouton-deverrouillage');

let deblocage_par_clic_effectue = false;
let game; 

// Réglages du jeu Snake
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
const loseSound = new Audio('lose.mp3');
const background = new Image();
background.src = 'pinguin.webp'; // Assurez-vous que cette image existe

// --- LOGIQUE DE DÉBLOCAGE MULTI-CONDITIONS ---

function gererAffichageBouton() {
    // Si le jeu est déjà lancé, on s'arrête
    if (deblocage_par_clic_effectue) {
        return; 
    }

    // 1. Vérification de la Condition Principale (Égalité stricte à 13)
    const scorePrincipal = parseInt(localStorage.getItem(CLE_DEBLOCAGE)) || 0;
    const condition1 = scorePrincipal === VALEUR_EXACTE_DEBLOCAGE;

    // 2. Vérification des 3 scores supplémentaires (Supérieur à 0)
    const score2 = parseInt(localStorage.getItem(CLE_SCORE_2)) || 0;
    const score3 = parseInt(localStorage.getItem(CLE_SCORE_3)) || 0;
    const score4 = parseInt(localStorage.getItem(CLE_SCORE_4)) || 0;

    const condition2_3_4 = score2 > 0 && score3 > 0 && score4 > 0;

    // 3. LA CONDITION GLOBALE : toutes les conditions doivent être VRAIES
    const deblocageTotal = condition1 && condition2_3_4;
    
    // --- GESTION DE L'AFFICHAGE ET DU MESSAGE ---
    if (deblocageTotal) {
        // Toutes les conditions sont VRAIES : afficher le bouton de déverrouillage
        messageDeblocage.style.display = 'none';
        boutonDeverrouillage.style.display = 'block';
        // Le canvas reste flou/grisé en attendant le clic
        canvas.style.filter = 'blur(5px) grayscale(100%)'; 
    } else {
        // Au moins une condition est FAUSSE : afficher le message de verrouillage
        messageDeblocage.style.display = 'block';
        boutonDeverrouillage.style.display = 'none';
        canvas.style.filter = 'blur(5px) grayscale(100%)';
        
        let messageHTML = "🔓 **Jeu Verrouillé !** 🔒<br>Vous devez remplir les conditions suivantes :<hr>";
        
        if (!condition1) {
            messageHTML += `❌ **Score Principal (${CLE_DEBLOCAGE}) :** Doit être **EXACTEMENT ${VALEUR_EXACTE_DEBLOCAGE}** (Actuel: ${scorePrincipal})<br>`;
        } else {
             messageHTML += `✅ **Score Principal :** OK<br>`;
        }

        if (score2 <= 0) {
            messageHTML += `❌ **Score Jeu 2 (${CLE_SCORE_2}) :** Doit être > 0 (Actuel: ${score2})<br>`;
        } else {
            messageHTML += `✅ **Score Jeu 2 :** OK<br>`;
        }
        if (score3 <= 0) {
            messageHTML += `❌ **Score Jeu 3 (${CLE_SCORE_3}) :** Doit être > 0 (Actuel: ${score3})<br>`;
        } else {
            messageHTML += `✅ **Score Jeu 3 :** OK<br>`;
        }
        if (score4 <= 0) {
            messageHTML += `❌ **Score Jeu 4 (${CLE_SCORE_4}) :** Doit être > 0 (Actuel: ${score4})<br>`;
        } else {
            messageHTML += `✅ **Score Jeu 4 :** OK<br>`;
        }
        
        messageDeblocage.innerHTML = messageHTML;
    }
}

// Fonction appelée lors du clic sur le bouton de déverrouillage
function lancerJeu() {
    // On vérifie de nouveau les conditions au moment du clic
    const scorePrincipal = parseInt(localStorage.getItem(CLE_DEBLOCAGE)) || 0;
    const score2 = parseInt(localStorage.getItem(CLE_SCORE_2)) || 0;
    const score3 = parseInt(localStorage.getItem(CLE_SCORE_3)) || 0;
    const score4 = parseInt(localStorage.getItem(CLE_SCORE_4)) || 0;
    
    const deblocageTotal = (scorePrincipal === VALEUR_EXACTE_DEBLOCAGE) && 
                           (score2 > 0 && score3 > 0 && score4 > 0);

    if (deblocageTotal && !deblocage_par_clic_effectue) {
        deblocage_par_clic_effectue = true;
        // Démarrer la boucle de jeu
        game = setInterval(draw, speed); 
        // Nettoyer l'interface de déblocage
        boutonDeverrouillage.style.display = 'none';
        canvas.style.filter = 'none';
        messageDeblocage.style.display = 'none';
    }
}

// Assigner la fonction de lancement au clic sur le bouton
boutonDeverrouillage.addEventListener('click', lancerJeu);

// Empêcher le mouvement si le jeu n'est pas lancé
document.addEventListener("keydown", (event) => {
    if (!deblocage_par_clic_effectue || gameOver) return;
    
    let key = event.keyCode;
    if (key == 37 && d != "RIGHT") d = "LEFT";
    else if (key == 38 && d != "DOWN") d = "UP";
    else if (key == 39 && d != "LEFT") d = "RIGHT";
    else if (key == 40 && d != "UP") d = "DOWN";
});


// --- FONCTIONS DE JEU (inchangées) ---

function roundRect(x, y, w, h, r, fillColor, strokeColor, lineWidth) {
    // ... code roundRect ...
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
    if (!deblocage_par_clic_effectue) {
        // Redessine le fond pour appliquer le filtre de flou si nécessaire
        ctx.drawImage(background, 0, 0, canvas.width, canvas.height);
        return; 
    }
    
    // ... Le reste de la logique du jeu (mouvement, nourriture, collision, dessin) ...

    ctx.drawImage(background, 0, 0, canvas.width, canvas.height);

    if(gameOver){
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

    let snakeX = snake[0].x;
    let snakeY = snake[0].y;

    if(d == "LEFT") snakeX -= box;
    if(d == "UP") snakeY -= box;
    if(d == "RIGHT") snakeX += box;
    if(d == "DOWN") snakeY += box;

    let ateFood = false;

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

    ctx.fillStyle = "orange";
    ctx.beginPath();
    ctx.arc(food.x + box/2, food.y + box/2, box/2, 0, Math.PI*2);
    ctx.fill();

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

    if(ateFood && speed > 30){
        speed -= 2;
        clearInterval(game);
        game = setInterval(draw, speed);
    }
}

function collision(head, array){
    return array.some(cell => head.x == cell.x && head.y == cell.y);
}

// Initialisation au chargement de la page
window.onload = () => {
    // Assure que l'image de fond est chargée avant le premier dessin
    background.onload = () => {
        // Vérifie les conditions du localStorage et gère l'affichage (message/bouton)
        gererAffichageBouton();

        // Dessine le canvas dans son état initial (flou/verrouillé)
        draw(); 
    };
}
</script>
</body>
</html>