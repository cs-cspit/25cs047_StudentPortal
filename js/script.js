// FAQ Toggle
function toggleAnswer(id) {
    var answer = document.getElementById(id);

    if (answer.style.display === "none") {
        answer.style.display = "block";
    } else {
        answer.style.display = "none";
    }
}

// Image Slider

let images = [
    "Photo/student3.png",
    "Photo/student2.png",
    "Photo/student.png"
];

let currentImage = 0;

function showImage() {
    document.getElementById("sliderImage").src = images[currentImage];
}

function nextImage() {
    currentImage++;

    if (currentImage >= images.length) {
        currentImage = 0;
    }

    showImage();
}

function previousImage() {
    currentImage--;

    if (currentImage < 0) {
        currentImage = images.length - 1;
    }

    showImage();
}
// Notification Banner
function closeNotification() {
    document.getElementById("notification").style.display = "none";
}

// Hamburger Menu
function toggleMenu() {
    document.getElementById("navbar").classList.toggle("show");
}

// Light/Dark Theme
function toggleTheme() {
    document.body.classList.toggle("dark");

    let button = document.getElementById("themeBtn");

    if (document.body.classList.contains("dark")) {
        button.textContent = "☀️ Light";
    } else {
        button.textContent = "🌙 Dark";
    }
}