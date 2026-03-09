const toggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.nav-links');

toggle.addEventListener('click', () => {
    nav.classList.toggle('active');
});

/* ---------- DROPDOWN MOBILE CLICK ---------- */
const dropdowns = document.querySelectorAll('.dropdown > a');

dropdowns.forEach(item => {
    item.addEventListener('click', function(e) {

        if (window.innerWidth <= 768) {
            e.preventDefault();
            this.parentElement.classList.toggle('active');
        }

    });
});