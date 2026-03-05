/*------------------Carousel------------------*/
const track = document.querySelector(".carousel-track");
const slides = document.querySelectorAll(".slide");
let index = 0;

document.querySelector(".next").onclick = () => {
    index = (index + 1) % slides.length;
    track.style.transform = `translateX(-${index * 100}%)`;
}

document.querySelector(".prev").onclick = () => {
    index = (index - 1 + slides.length) % slides.length;
    track.style.transform = `translateX(-${index * 100}%)`;
}

setInterval(() => {
    index = (index + 1) % slides.length;
    track.style.transform = `translateX(-${index * 100}%)`;
}, 3000);
/*-------------------------------------------*/