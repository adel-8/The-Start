document.addEventListener('DOMContentLoaded', function() {
    // ----- Hero Slider -----
    let slideIndex = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.dot');

    function showSlide(n) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (dots[i]) dots[i].classList.remove('active');
        });
        slides[n].classList.add('active');
        if (dots[n]) dots[n].classList.add('active');
        slideIndex = n;
    }

    function nextSlide() {
        let next = (slideIndex + 1) % slides.length;
        showSlide(next);
    }

    let interval = setInterval(nextSlide, 10000);

    if (dots.length) {
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                clearInterval(interval);
                showSlide(i);
                interval = setInterval(nextSlide, 10000);
            });
        });
    }

    const slider = document.querySelector('.hero-slider');
    if (slider) {
        slider.addEventListener('mouseenter', () => clearInterval(interval));
        slider.addEventListener('mouseleave', () => {
            interval = setInterval(nextSlide, 10000);
        });
    }

    // ----- More Details Buttons (redirect) -----
    document.querySelectorAll('.details-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const slug = this.getAttribute('data-slug');
            if (slug) {
                window.location.href = `/product/${slug}`;
            }
        });
    });
});