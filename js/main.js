(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();


    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').addClass('shadow-sm').css('top', '0px');
        } else {
            $('.sticky-top').removeClass('shadow-sm').css('top', '-100px');
        }
    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1500, 'easeInOutExpo');
        return false;
    });


    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });


    // Portfolio isotope and filter
    var portfolioIsotope = $('.portfolio-container').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
    });
    $('#portfolio-flters li').on('click', function () {
        $("#portfolio-flters li").removeClass('active');
        $(this).addClass('active');

        portfolioIsotope.isotope({ filter: $(this).data('filter') });
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        items: 1,
        dots: false,
        loop: true,
        nav: true,
        navText: [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


})(jQuery);

// Add interactive animation to the counter
const counter = document.querySelector('.year-number');
let counted = false;

function animateValue(element, start, end, duration) {
    if (counted) return;

    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
    counted = true;
}

// Animate counter when it comes into view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateValue(counter, 0, 5, 2000);
        }
    });
}, { threshold: 0.5 });

observer.observe(counter);

// Add benefit item animations
const benefitItems = document.querySelectorAll('.benefit-item');
benefitItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
        item.style.transform = 'translateX(5px)';
        item.style.backgroundColor = 'rgba(76, 175, 80, 0.05)';
    });

    item.addEventListener('mouseleave', () => {
        item.style.transform = 'translateX(0)';
        item.style.backgroundColor = '';
    });
});

// Add image hover effect
const productImage = document.querySelector('.product-image');
productImage.addEventListener('mouseenter', () => {
    productImage.style.transform = 'perspective(1000px) rotateY(10deg) scale(1.03)';
    productImage.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
});

productImage.addEventListener('mouseleave', () => {
    productImage.style.transform = 'perspective(1000px) rotateY(0deg) scale(1)';
    productImage.style.boxShadow = '0 15px 35px rgba(0,0,0,0.1)';
});