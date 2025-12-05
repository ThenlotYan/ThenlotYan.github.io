const textarea = document.getElementById("TA");

const map = {
    "a": "a",
    "z": "b",
    "e": "c",
    "r": "d",
    "t": "e",
    "y": "f",
    "u": "g",
    "i": "h",
    "o": "i",
    "p": "j",

    "q": "k",
    "s": "l",
    "d": "m",
    "f": "n",
    "g": "o",
    "h": "p",
    "j": "q",
    "k": "r",
    "l": "s",
    "m": "t",

    "w": "u",
    "x": "v",
    "c": "w",
    "v": "x",
    "b": "y",
    "n": "z"
};

textarea.addEventListener("keydown", function (e) {
    const key = e.key;


    const specialKeys = [
        "Backspace","Delete","Tab","Enter",
        "ArrowLeft","ArrowRight","ArrowUp","ArrowDown",
        "Escape","Shift","Control","Alt","Meta"
    ];

    if (specialKeys.includes(key)) return;

    const lower = key.toLowerCase();


    if (map[lower]) {
        e.preventDefault();

        let output = map[lower];
        if (key === key.toUpperCase()) {
            output = output.toUpperCase();
        }

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        textarea.value =
            textarea.value.substring(0, start) +
            output +
            textarea.value.substring(end);

        textarea.selectionStart = textarea.selectionEnd = start + output.length;
    }
});


