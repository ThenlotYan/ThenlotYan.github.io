// ==== Split.js config (garder) ====
Split(['#left','#right'],{ sizes:[50,50], minSize:150, gutterSize:5 });
Split(['#top','#bottom'],{ direction:'vertical', sizes:[75,25], minSize:150, gutterSize:5 });

console.log("📌 Layout Ready.");
document.getElementById("terminal").innerHTML += "<br>> UI loaded";

// 🎵 === AUDIO PLAYER & partage analyser ===
let audio = new Audio();
audio.volume = 0.8;

const terminal = document.getElementById("terminal");
const radios = document.querySelectorAll('input[name="music"]');

let analyser = null;
let audioCtx = null;

// Crée analyseur partagé
async function ensureAnalyser(){
    if(analyser) return;
    audioCtx = new (window.AudioContext||webkitAudioContext)();
    let src = audioCtx.createMediaElementSource(audio);
    analyser = audioCtx.createAnalyser();
    analyser.fftSize = 256;
    src.connect(analyser);
    src.connect(audioCtx.destination);
}

// ===== Visualizer 1 (BARRES) =====
const canvas1 = document.getElementById("visualizer2d");
const ctx1 = canvas1.getContext("2d");

function resize(){
    canvas1.width = canvas1.clientWidth;
    canvas1.height = canvas1.clientHeight;
    canvas2.width = canvas2.clientWidth;
    canvas2.height = canvas2.clientHeight;
}
window.addEventListener("resize", resize);

// ===== Visualizer 2 (CERCLE RÉACTIF) =====
const canvas2 = document.getElementById("visualizer3d");
const ctx2 = canvas2.getContext("2d");

function draw(){
    requestAnimationFrame(draw);
    if(!analyser) return;

    let buffer = new Uint8Array(analyser.frequencyBinCount);
    analyser.getByteFrequencyData(buffer);

    /* ==== LEFT VISUALIZER : BARRES ==== */
    ctx1.fillStyle="#000";
    ctx1.fillRect(0,0,canvas1.width,canvas1.height);
    let bw=canvas1.width/buffer.length;
    buffer.forEach((v,i)=>{
        ctx1.fillStyle=`hsl(${i*360/buffer.length},100%,50%)`;
        let h = v/255*canvas1.height;
        ctx1.fillRect(i*bw,canvas1.height-h,bw-1,h);
    });

    /* ==== RIGHT VISUALIZER : CERCLE ==== */
    ctx2.fillStyle="#050505";
    ctx2.fillRect(0,0,canvas2.width,canvas2.height);

    let cx=canvas2.width/2, cy=canvas2.height/2;
    let radius = 50 + buffer[5]; // bat avec les basses

    ctx2.beginPath();
    ctx2.arc(cx,cy,radius,0,Math.PI*2);
    ctx2.strokeStyle=`hsl(${buffer[10]*2},100%,60%)`;
    ctx2.lineWidth=4;
    ctx2.stroke();

    // onde autour
    ctx2.beginPath();
    for(let i=0;i<buffer.length;i++){
        let angle=(i/buffer.length)*Math.PI*2;
        let r = radius + buffer[i]/3;
        let x=cx+Math.cos(angle)*r;
        let y=cy+Math.sin(angle)*r;
        i===0 ? ctx2.moveTo(x,y) : ctx2.lineTo(x,y);
    }
    ctx2.closePath();
    ctx2.strokeStyle=`hsl(${buffer[30]*2},100%,50%)`;
    ctx2.lineWidth=2;
    ctx2.stroke();
}
draw();
resize();

// === RADIO MUSIC PLAY ===
async function onMusicSelect(choice){
    audio.src=`musics/${choice}.mp3`;
    await ensureAnalyser();

    if(audioCtx.state==="suspended") await audioCtx.resume();
    audio.play();

    terminal.innerHTML+=`<br>> Playing: ${choice}`;
    terminal.scrollTop=terminal.scrollHeight;
}

radios.forEach(r=>{
    r.addEventListener("change",()=>onMusicSelect(r.value));
});


// ---- Setup canvas 3 (waveform) ----
const canvas3 = document.getElementById("visualizer3");
const ctx3 = canvas3.getContext("2d");

function resize3(){
    canvas3.width = canvas3.clientWidth;
    canvas3.height = canvas3.clientHeight;
}
window.addEventListener("resize", resize3);
resize3();

function draw3(){
    requestAnimationFrame(draw3);
    if(!analyser) return;

    let waveform = new Uint8Array(analyser.frequencyBinCount);
    analyser.getByteTimeDomainData(waveform);

    ctx3.fillStyle = "#000";
    ctx3.fillRect(0, 0, canvas3.width, canvas3.height);

    ctx3.lineWidth = 2;
    ctx3.strokeStyle = "hsl(200, 100%, 60%)";

    ctx3.beginPath();
    let sliceWidth = canvas3.width / waveform.length;
    let x = 0;

    for(let i=0; i<waveform.length; i++){
        let v = waveform[i] / 128.0;   // 0..2
        let y = v * canvas3.height/2;  // centre vertical

        if(i===0){
            ctx3.moveTo(x,y);
        } else {
            ctx3.lineTo(x,y);
        }
        x += sliceWidth;
    }
    ctx3.lineTo(canvas3.width, canvas3.height/2);
    ctx3.stroke();
}
draw3();



const fileInput = document.getElementById('file-input');

fileInput.addEventListener('change', async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('audio/')) {
    alert("Merci de choisir un fichier audio valide !");
    return;
  }

  // Crée un URL temporaire pour le fichier
  const fileURL = URL.createObjectURL(file);

  // Stop l'audio en cours (si besoin)
  audio.pause();

  // Charge le fichier dans l'audio
  audio.src = fileURL;

  try {
    await ensureSharedAnalyser();
    if (audioCtxRef && audioCtxRef.state === 'suspended') {
      await audioCtxRef.resume();
    }
  } catch (e) {
    console.warn("Erreur lors de l'activation de l'analyseur partagé :", e);
  }

  try {
    await audio.play();
  } catch (e) {
    console.warn("Erreur lors de la lecture du fichier audio :", e);
  }

  terminal.innerHTML += `<br>> Lecture du fichier : ${file.name}`;
  terminal.scrollTop = terminal.scrollHeight;
});




Split(['#left', '#middle', '#right'], {
  sizes: [33, 34, 33],
  minSize: 150,
  gutterSize: 5
});

// ==== Setup commun ====
const canvas2d = document.getElementById("visualizer2d");
const ctxBars = canvas2d.getContext("2d");

const canvasCircle = document.getElementById("visualizerCircle");
const ctxCircle = canvasCircle.getContext("2d");

const canvasSquare = document.getElementById("visualizerSquare");
const ctxSquare = canvasSquare.getContext("2d");

function resizeCanvas(canvas) {
  const rect = canvas.getBoundingClientRect();
  canvas.width = Math.max(1, Math.floor(rect.width));
  canvas.height = Math.max(1, Math.floor(rect.height));
}
window.addEventListener('resize', () => {
  resizeCanvas(canvas2d);
  resizeCanvas(canvasCircle);
  resizeCanvas(canvasSquare);
});
resizeCanvas(canvas2d);
resizeCanvas(canvasCircle);
resizeCanvas(canvasSquare);

let buffer = null;

// ==== Visualizer 2D Bars ====
function drawBars(){
  requestAnimationFrame(drawBars);
  if(!analyserRef) return;
  if(!buffer || buffer.length !== analyserRef.frequencyBinCount){
    buffer = new Uint8Array(analyserRef.frequencyBinCount);
  }
  analyserRef.getByteFrequencyData(buffer);

  ctxBars.fillStyle = "#000";
  ctxBars.fillRect(0,0,canvas2d.width,canvas2d.height);

  let barWidth = canvas2d.width / buffer.length;

  for(let i=0; i<buffer.length; i++){
    let h = buffer[i]/255 * canvas2d.height;
    ctxBars.fillStyle = `hsl(${i*360/buffer.length},100%,50%)`;
    ctxBars.fillRect(i*barWidth, canvas2d.height - h, Math.max(1, barWidth-2), h);
  }
}
drawBars();

// ==== Visualizer Cercle ====
function drawCircle(){
  requestAnimationFrame(drawCircle);
  if(!analyserRef) return;
  if(!buffer || buffer.length !== analyserRef.frequencyBinCount){
    buffer = new Uint8Array(analyserRef.frequencyBinCount);
  }
  analyserRef.getByteFrequencyData(buffer);

  ctxCircle.fillStyle = "#050505";
  ctxCircle.fillRect(0,0,canvasCircle.width,canvasCircle.height);

  let cx = canvasCircle.width/2;
  let cy = canvasCircle.height/2;
  let radius = 50 + buffer[5];

  // cercle extérieur
  ctxCircle.beginPath();
  ctxCircle.arc(cx, cy, radius, 0, Math.PI*2);
  ctxCircle.strokeStyle = `hsl(${buffer[10]*2},100%,60%)`;
  ctxCircle.lineWidth = 4;
  ctxCircle.stroke();

  // onde autour
  ctxCircle.beginPath();
  for(let i=0; i<buffer.length; i++){
    let angle = (i/buffer.length) * Math.PI*2;
    let r = radius + buffer[i]/3;
    let x = cx + Math.cos(angle)*r;
    let y = cy + Math.sin(angle)*r;
    i === 0 ? ctxCircle.moveTo(x,y) : ctxCircle.lineTo(x,y);
  }
  ctxCircle.closePath();
  ctxCircle.strokeStyle = `hsl(${buffer[30]*2},100%,50%)`;
  ctxCircle.lineWidth = 2;
  ctxCircle.stroke();
}
drawCircle();
