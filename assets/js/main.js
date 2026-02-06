document.addEventListener('DOMContentLoaded', function() {
    
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Produit ajouté au panier !', 'success');
                    updateCartCount(data.cartCount);
                } else {
                    showAlert(data.message || 'Une erreur est survenue', 'danger');
                }
            })
            .catch(error => {
                showAlert('Une erreur est survenue', 'danger');
            });
        });
    });

    const quantityInputs = document.querySelectorAll('.quantity-change');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const panierId = this.dataset.id;
            const quantite = this.value;
            
            fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `panier_id=${panierId}&quantite=${quantite}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    const deleteButtons = document.querySelectorAll('.delete-cart-item');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Supprimer cet article ?')) {
                const panierId = this.dataset.id;
                
                fetch('ajax/delete_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `panier_id=${panierId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    });
});

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-floating alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function updateCartCount(count) {
    const badge = document.querySelector('.navbar .badge');
    if (badge) {
        badge.textContent = count;
    } else if (count > 0) {
        const cartLink = document.querySelector('a[href*="panier"]');
        if (cartLink) {
            const newBadge = document.createElement('span');
            newBadge.className = 'badge bg-danger';
            newBadge.textContent = count;
            cartLink.appendChild(newBadge);
        }
    }
}
