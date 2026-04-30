// script.js — LibreChange — Scripts JavaScript globaux
// MGSI Groupe 45 | MAHIR Rabia & ABLAD Mostapha

document.addEventListener('DOMContentLoaded', function () {



    function loadNotifications() {
        fetch('<?= BASE_URL ?>pages/notifications_count.php')
            .then(res => res.text())
            .then(data => {
                document.getElementById("notif-badge").innerHTML = data;
            });
    }

    setInterval(loadNotifications, 5000);

    // ========================================
    // IMAGE PREVIEW FIX (AJOUT LIVRE)
    // ========================================

    const imageInput = document.getElementById('image');
    const preview = document.getElementById('preview');

    if (imageInput && preview) {

        imageInput.addEventListener('change', function (event) {

            const file = event.target.files[0];

            if (file) {

                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                };

                reader.readAsDataURL(file);

            } else {
                preview.src = "../images/default-book.jfif";
            }

        });

    }


    // ========================================
    // 1. Confirmation avant suppression
    // ========================================
    document.querySelectorAll('.btn-danger[type="submit"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var msg = btn.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ========================================
    // 2. Faire défiler les messages vers le bas
    // ========================================
    var messagesList = document.querySelector('.messages-list');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }

    // ========================================
    // 3. Fermer les alertes automatiquement
    // ========================================
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 4000);
    });

    // ========================================
    // 4. Prévisualisation image (upload)
    // ========================================
    var imgInput = document.getElementById('image_upload');
    if (imgInput) {
        imgInput.addEventListener('change', function () {
            var preview = document.getElementById('img_preview');
            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // ========================================
    // 5. Highlight lien actif dans la navbar
    // ========================================
    var currentPath = window.location.pathname;
    document.querySelectorAll('.nav-links a').forEach(function (link) {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
            link.style.background = 'rgba(255,255,255,0.22)';
        }
    });


    const stars = document.querySelectorAll('.star');
    const input = document.getElementById('note-value');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            let value = star.getAttribute('data-value');
            input.value = value;

            stars.forEach(s => {
                s.classList.remove('fa-solid');
                s.classList.add('fa-regular');
            });

            for (let i = 0; i < value; i++) {
                stars[i].classList.remove('fa-regular');
                stars[i].classList.add('fa-solid', 'active');
            }
        });
    });

    const toggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("nav-links");

    toggle.addEventListener("click", () => {
        nav.classList.toggle("open");
    });

});
