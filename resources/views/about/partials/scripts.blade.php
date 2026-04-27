<script>
window.__ABOUT_PUROK_MAPS__ = @json($purokMapUrls ?? []);
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.fade-in').forEach(function(el) {
        observer.observe(el);
    });

    var purokSelect = document.getElementById('purok-select');
    if (purokSelect) {
        purokSelect.addEventListener('change', function() {
            var mapEl = document.getElementById('barangay-map');
            if (!mapEl) return;
            var purokMaps = window.__ABOUT_PUROK_MAPS__ || {};
            mapEl.src = purokMaps[this.value] || purokMaps['default'] || mapEl.src;
        });
    }

    var slides = Array.from(document.querySelectorAll('#about-slideshow .about-slide'));
    var dots = Array.from(document.querySelectorAll('#about-slideshow .about-slide-dot'));
    var prevBtn = document.getElementById('about-slide-prev');
    var nextBtn = document.getElementById('about-slide-next');
    var activeSlide = 0;
    var slideTimer = null;

    function showSlide(index) {
        if (!slides.length) return;
        activeSlide = (index + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
            slide.classList.toggle('hidden', i !== activeSlide);
        });
        dots.forEach(function (dot, i) {
            dot.classList.toggle('bg-white', i === activeSlide);
            dot.classList.toggle('bg-white/50', i !== activeSlide);
        });
    }

    function startAutoSlide() {
        if (!slides.length) return;
        if (slideTimer) clearInterval(slideTimer);
        slideTimer = setInterval(function () {
            showSlide(activeSlide + 1);
        }, 4000);
    }

    if (slides.length && prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function () {
            showSlide(activeSlide - 1);
            startAutoSlide();
        });
        nextBtn.addEventListener('click', function () {
            showSlide(activeSlide + 1);
            startAutoSlide();
        });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showSlide(Number(dot.dataset.slideIndex || 0));
                startAutoSlide();
            });
        });
        showSlide(0);
        startAutoSlide();
    }

    var officialSlides = Array.from(document.querySelectorAll('#officials-carousel .officials-slide'));
    var officialDots = Array.from(document.querySelectorAll('#officials-carousel .officials-slide-dot'));
    var officialPrevBtn = document.getElementById('officials-slide-prev');
    var officialNextBtn = document.getElementById('officials-slide-next');
    var activeOfficialSlide = 0;
    var officialTimer = null;

    function showOfficialSlide(index) {
        if (!officialSlides.length) return;
        activeOfficialSlide = (index + officialSlides.length) % officialSlides.length;

        officialSlides.forEach(function (slide, i) {
            slide.classList.toggle('hidden', i !== activeOfficialSlide);
        });
        officialDots.forEach(function (dot, i) {
            dot.classList.toggle('bg-blue-600', i === activeOfficialSlide);
            dot.classList.toggle('bg-gray-300', i !== activeOfficialSlide);
        });
    }

    function startOfficialAutoSlide() {
        if (officialSlides.length <= 1) return;
        if (officialTimer) clearInterval(officialTimer);
        officialTimer = setInterval(function () {
            showOfficialSlide(activeOfficialSlide + 1);
        }, 5000);
    }

    if (officialSlides.length) {
        if (officialPrevBtn) {
            officialPrevBtn.addEventListener('click', function () {
                showOfficialSlide(activeOfficialSlide - 1);
                startOfficialAutoSlide();
            });
        }
        if (officialNextBtn) {
            officialNextBtn.addEventListener('click', function () {
                showOfficialSlide(activeOfficialSlide + 1);
                startOfficialAutoSlide();
            });
        }
        officialDots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showOfficialSlide(Number(dot.dataset.slideIndex || 0));
                startOfficialAutoSlide();
            });
        });
        showOfficialSlide(0);
        startOfficialAutoSlide();
    }
});
</script>
