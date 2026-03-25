/* ============================================
   NOVA — JavaScript (Animations + AJAX)
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

    // ── Scroll animations (Intersection Observer) ──────
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in, .scale-in, .stagger-children').forEach(el => {
        observer.observe(el);
    });

    // ── Mobile nav toggle ──────────────────────────────
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            navToggle.querySelector('i').classList.toggle('bi-list');
            navToggle.querySelector('i').classList.toggle('bi-x-lg');
        });
    }

    // ── Flash message auto-dismiss ─────────────────────
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => flash.remove(), 4000);
    }

    // ── AJAX: Add to cart ──────────────────────────────
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const articleId = btn.dataset.id;
            const qtyInput = document.querySelector(`#qty-${articleId}`);
            const quantity = qtyInput ? qtyInput.value : 1;

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Ajout...';

            try {
                const res = await fetch('ajax/add_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `article_id=${articleId}&quantite=${quantity}`
                });
                const data = await res.json();

                if (data.success) {
                    showToast('success', data.message || 'Ajouté au panier');
                    // Update cart badge
                    const badge = document.querySelector('.nav-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    }
                } else {
                    showToast('error', data.message || 'Erreur');
                }
            } catch {
                showToast('error', 'Erreur de connexion');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-plus"></i> Ajouter au panier';
        });
    });

    // ── AJAX: Update cart quantity ──────────────────────
    document.querySelectorAll('.cart-qty-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const panierId = btn.dataset.id;
            const action = btn.dataset.action; // 'increase' or 'decrease'

            try {
                const res = await fetch('ajax/update_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `panier_id=${panierId}&action=${action}`
                });
                const data = await res.json();

                if (data.success) {
                    location.reload();
                } else {
                    showToast('error', data.message || 'Erreur');
                }
            } catch {
                showToast('error', 'Erreur de connexion');
            }
        });
    });

    // ── AJAX: Delete cart item ─────────────────────────
    document.querySelectorAll('.cart-delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const panierId = btn.dataset.id;

            try {
                const res = await fetch('ajax/delete_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `panier_id=${panierId}`
                });
                const data = await res.json();

                if (data.success) {
                    location.reload();
                } else {
                    showToast('error', data.message || 'Erreur');
                }
            } catch {
                showToast('error', 'Erreur de connexion');
            }
        });
    });

    // ── AJAX: Toggle favorite ──────────────────────────
    document.querySelectorAll('.fav-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const articleId = btn.dataset.id;

            try {
                const res = await fetch('ajax/toggle_favori.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `article_id=${articleId}`
                });
                const data = await res.json();

                if (data.success) {
                    btn.classList.toggle('active');
                    btn.classList.add('pop');
                    setTimeout(() => btn.classList.remove('pop'), 300);
                    const icon = btn.querySelector('i');
                    if (data.favorited) {
                        icon.classList.replace('bi-heart', 'bi-heart-fill');
                    } else {
                        icon.classList.replace('bi-heart-fill', 'bi-heart');
                    }
                } else {
                    showToast('error', data.message || 'Connectez-vous pour ajouter aux favoris');
                }
            } catch {
                showToast('error', 'Erreur de connexion');
            }
        });
    });

    // ── Tabs ───────────────────────────────────────────
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            const parent = tab.closest('.tabs-container');
            if (!parent) return;

            parent.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            parent.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            const content = parent.querySelector(`#${target}`);
            if (content) content.classList.add('active');
        });
    });

    // ── Image upload preview ───────────────────────────
    const imageUpload = document.querySelector('.image-upload');
    const imageInput = document.querySelector('#image-input');

    if (imageUpload && imageInput) {
        imageUpload.addEventListener('click', () => imageInput.click());
        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = 'var(--accent)';
        });
        imageUpload.addEventListener('dragleave', () => {
            imageUpload.style.borderColor = '';
        });
        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = '';
            if (e.dataTransfer.files.length) {
                imageInput.files = e.dataTransfer.files;
                previewImage(e.dataTransfer.files[0]);
            }
        });
        imageInput.addEventListener('change', () => {
            if (imageInput.files.length) {
                previewImage(imageInput.files[0]);
            }
        });
    }

    function previewImage(file) {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUpload.innerHTML = `<img src="${e.target.result}" style="max-height: 200px; border-radius: 12px; margin: 0 auto;">`;
        };
        reader.readAsDataURL(file);
    }

    // ── Toast notification system ──────────────────────
    window.showToast = function(type, message) {
        const existing = document.querySelector('.flash');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `flash flash-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 4000);
    };

    // ── Quantity selector for product page ─────────────
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const input = selector.querySelector('input');
        const minusBtn = selector.querySelector('.qty-minus');
        const plusBtn = selector.querySelector('.qty-plus');
        const max = parseInt(input.dataset.max) || 99;

        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                let val = parseInt(input.value);
                if (val > 1) input.value = val - 1;
            });
        }
        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                let val = parseInt(input.value);
                if (val < max) input.value = val + 1;
            });
        }
    });

    // ── Dark Mode Toggle ──────────────────────────────────
    const darkToggle = document.getElementById('darkModeToggle');
    const htmlEl = document.documentElement;
    
    // Load saved preference
    const savedTheme = localStorage.getItem('nova-theme');
    if (savedTheme === 'dark') {
        htmlEl.setAttribute('data-theme', 'dark');
        if (darkToggle) {
            const icon = darkToggle.querySelector('i');
            if (icon) { icon.className = 'bi bi-sun'; }
        }
    }
    
    if (darkToggle) {
        darkToggle.addEventListener('click', function() {
            const isDark = htmlEl.getAttribute('data-theme') === 'dark';
            const icon = darkToggle.querySelector('i');
            
            if (isDark) {
                htmlEl.removeAttribute('data-theme');
                localStorage.setItem('nova-theme', 'light');
                if (icon) icon.className = 'bi bi-moon';
            } else {
                htmlEl.setAttribute('data-theme', 'dark');
                localStorage.setItem('nova-theme', 'dark');
                if (icon) icon.className = 'bi bi-sun';
            }
        });
    }

    // ── Notification Dropdown ─────────────────────────────
    const bell = document.getElementById('notifBell');
    const dropdown = document.getElementById('notifDropdown');
    
    if (bell && dropdown) {
        bell.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && e.target !== bell && !bell.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Mark all read
        const markReadBtn = document.getElementById('markAllRead');
        if (markReadBtn) {
            markReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fetch(this.getAttribute('href'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Remove badge
                        const badge = bell.querySelector('.notif-badge');
                        if (badge) badge.remove();
                        // Remove unread styling
                        dropdown.querySelectorAll('.notif-item.unread').forEach(el => {
                            el.classList.remove('unread');
                        });
                    }
                })
                .catch(() => {});
            });
        }
    }

});

// ── CSS for spinner ────────────────────────────────────
const style = document.createElement('style');
style.textContent = `
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .spin { animation: spin 0.6s linear infinite; }
`;
document.head.appendChild(style);
