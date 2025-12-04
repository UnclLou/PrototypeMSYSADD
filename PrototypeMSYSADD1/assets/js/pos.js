document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    const cartItems = document.querySelector('.cart-items');
    const totalAmount = document.getElementById('total-amount');
    const checkoutBtn = document.querySelector('.checkout-btn');
    const filterButtons = document.querySelectorAll('.filter-btn');

    // Category filtering
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            document.querySelectorAll('.product-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productId = productCard.dataset.id;
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = parseFloat(productCard.dataset.price);
            const stock = parseInt(productCard.querySelector('.stock').textContent.split(': ')[1]);

            // Check if product is already in cart
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                    updateCart();
                } else {
                    alert('Not enough stock available!');
                }
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                });
                updateCart();
            }
        });
    });

    // Update cart display
    function updateCart() {
        cartItems.innerHTML = '';
        let total = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-details">
                    <span class="item-name">${item.name}</span>
                    <div class="item-quantity">
                        <button class="quantity-btn minus" data-id="${item.id}">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn plus" data-id="${item.id}">+</button>
                    </div>
                    <input type="text" class="item-instructions" data-id="${item.id}" placeholder="Special instructions (optional)" value="${item.instructions || ''}">
                </div>
                <div class="cart-item-price">
                    <span>₱${itemTotal.toFixed(2)}</span>
                    <button class="remove-item" data-id="${item.id}">×</button>
                </div>
            `;
            cartItems.appendChild(cartItem);
        });

        totalAmount.textContent = total.toFixed(2);

        // Add event listeners for instruction inputs
        cartItems.querySelectorAll('.item-instructions').forEach(input => {
            input.addEventListener('input', function() {
                const productId = this.dataset.id;
                const item = cart.find(item => item.id === productId);
                if (item) {
                    item.instructions = this.value;
                }
            });
        });
    }

    // Handle quantity changes and item removal
    cartItems.addEventListener('click', function(e) {
        const target = e.target;
        const productId = target.dataset.id;

        if (target.classList.contains('remove-item')) {
            const index = cart.findIndex(item => item.id === productId);
            if (index > -1) {
                cart.splice(index, 1);
                updateCart();
            }
        } else if (target.classList.contains('quantity-btn')) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                if (target.classList.contains('plus')) {
                    const stock = parseInt(document.querySelector(`.product-card[data-id="${productId}"] .stock`).textContent.split(': ')[1]);
                    if (item.quantity < stock) {
                        item.quantity++;
                        updateCart();
                    } else {
                        alert('Not enough stock available!');
                    }
                } else if (target.classList.contains('minus')) {
                    if (item.quantity > 1) {
                        item.quantity--;
                        updateCart();
                    } else {
                        const index = cart.findIndex(i => i.id === productId);
                        cart.splice(index, 1);
                        updateCart();
                    }
                }
            }
        }
    });

    // Process order
    checkoutBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Please add items to cart first!');
            return;
        }

        // Send order to server
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                items: cart
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order processed successfully!');
                cart.length = 0;
                updateCart();
                // Refresh the page to update stock
                location.reload();
            } else {
                alert('Error processing order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing order. Please try again.');
        });
    });
}); 