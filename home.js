document.addEventListener("DOMContentLoaded", function () {
    // Slider de imágenes
    new Swiper(".mySwiper", {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        pagination: { el: ".swiper-pagination", clickable: true },
        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        breakpoints: {
            320: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 3 }
        }
    });

    // Slider de videos
    new Swiper(".mySwiperVideos", {
        slidesPerView: 2,
        spaceBetween: 20,
        loop: true,
        autoHeight: true, // <-- Esto ajusta la altura según cada slide
        pagination: { el: ".swiper-pagination", clickable: true },
        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        breakpoints: {
            320: { slidesPerView: 1 },
            768: { slidesPerView: 1 },
            1024: { slidesPerView: 2 }
        }
    });


    // Lightbox para imágenes y videos
    GLightbox({ selector: ".glightbox" });

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const logoutUrl = logoutBtn.getAttribute('data-url');

            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Se cerrará tu sesión actual.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        });
    }


});
