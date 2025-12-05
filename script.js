const terminalBody = document.querySelector(".terminal-body");

const fichiersValides = [
    "snake.html",
    "2048.html",
    "flappy-penguin.html"
];

const datesFichiers = [
    "22/10/2025 14:49",
    "24/09/2025 11:20",
    "18/08/2025 16:05"
];

let historiqueCmd = [];
let indexHistorique = -1;


// Crée une nouvelle ligne de commande
function createNewCommandLine() {
    const line = document.createElement("div");
    line.classList.add("command-line");

    // Prompt complet comme sous Ubuntu
    line.innerHTML = `<span class="prompt">user@linux-desktop: ~$</span> <span class="input" contenteditable="true"></span>`;
    
    terminalBody.appendChild(line);

    const input = line.querySelector(".input");
    input.focus();

    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const command = input.innerText.trim();
            if(command !== "") {
                historiqueCmd.push(command);
                indexHistorique = historiqueCmd.length; // position après dernière commande
            }
            executeCommand(command, line);
        } 
        else if (e.key === "ArrowUp") {
            e.preventDefault();
            if (historiqueCmd.length === 0){
                // Si pas d’historique, afficher HELP automatiquement
                input.innerText = "HELP";
                placeCaretAtEnd(input);
                indexHistorique = 0; // index sur HELP
            } else if (indexHistorique > 0) {
                indexHistorique--;
                input.innerText = historiqueCmd[indexHistorique];
                placeCaretAtEnd(input);
            }
        }
        else if (e.key === "ArrowDown") {
            e.preventDefault();
            if (historiqueCmd.length === 0) return;

            if (indexHistorique < historiqueCmd.length - 1) {
                indexHistorique++;
                input.innerText = historiqueCmd[indexHistorique];
            } else {
                indexHistorique = historiqueCmd.length;
                input.innerText = "";
            }
            placeCaretAtEnd(input);
        }
    });

}


// Exécute la commande
function executeCommand(command, line) {

    // Affichage format command
    let output = document.createElement("div");
    output.classList.add("output");

    if (command === "") {
        // rien
    } else if (command.toUpperCase() === "HELP") {
        afficherHelp();
    } else if (command.toUpperCase() === "CLEAR") {
        clearTerminal();
        return; // stoppe ici pour pas créer double ligne
    } else if (command.toUpperCase() === "LS") {
        afficherLS();
    } else if (command.toUpperCase() === "ABOUT") {
        afficherAbout();
    } else if (command.startsWith("./")) {
        ouvrirFichier(command);
    } else {
        afficherInconnu(command);
    }




    terminalBody.appendChild(output);

    // Génère une nouvelle ligne après la réponse
    createNewCommandLine();
    terminalBody.scrollTop = terminalBody.scrollHeight;
}

// Première ligne au chargement
createNewCommandLine();





function afficherHelp() {
    const helpText = `
Commandes disponibles:
  HELP           - Affiche cette aide
  ABOUT          - Infos sur "Les 4 gars & 4 Zigotos"
  LS             - Montre les dossiers à ouvrir
  CLEAR          - Efface tout le CMD
  ./fichier      - Ouvre la page fichier.html
`;
    ajouterTexte(helpText);
}


function ouvrirFichier(cmd) {
    const baseName = cmd.slice(2); // nom après ./
    
    if (baseName === "quiz") {
        ajouterTexte(`Ouverture de quiz obsolescence/index.html ...`);
        setTimeout(() => {
            window.location.href = "quiz obsolescence/index.html";
        }, 700);
    } else {
        const filename = baseName + ".html";
        if (fichiersValides.includes(filename)) {
            ajouterTexte(`Ouverture de ${filename} ...`);
            setTimeout(() => {
                window.location.href = filename;
            }, 700);
        } else {
            ajouterTexte(`Erreur : le fichier "${filename}" n'existe pas.`);
        }
    }
}



function afficherInconnu(cmd) {
    ajouterTexte(`Commande inconnue: "${cmd}" (tape HELP)`);
}

// Fonction utilitaire pour afficher du texte dans le terminal
function ajouterTexte(txt) {
    let output = document.createElement("div");
    output.classList.add("output");
    output.innerText = txt;
    terminalBody.appendChild(output);
}

function clearTerminal() {
    terminalBody.innerHTML = "";  // vide tout le terminal, y compris input
    createNewCommandLine();       // crée une nouvelle ligne de commande propre
}

function afficherLS() {
    let lignes = fichiersValides.map((nom, index) => {
        const type = 'f'; // f = fichier
        const espacesType = '   '; // 7 espaces après le type
        const espacesDate = '   ';   // 5 espaces après la date
        const date = datesFichiers[index] || "??/??/???? ??:??";
        return `${type}${espacesType}${date}${espacesDate}${nom}`;
    });

    ajouterTexte(lignes.join('\n'));
}



function afficherAbout() {
    const membres = [
        "Gars : Thenlot Yan",
        "Gars : Loic Claude",
        "Gars : Louis Mathis",
        "Gars : Wickers Nolann",
        "Zigotos : Ribeiro Chloé",
        "Zigotos : montabord Marc-Alexandre",
        "Zigotos : Michel Jonathan",
        "Zigotos : Fontaine Romain"
    ];
    const txt = "Les 4 gars & 4 Zigotos :\n" + membres.join("\n");
    ajouterTexte(txt);
}


function placeCaretAtEnd(el) {
    el.focus();
    if (typeof window.getSelection != "undefined"
        && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }
}




