// Kiosk page JavaScript goes here

document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    const kioskProductsGrid = document.querySelector('.kiosk-products-grid');
    const kioskCartItems = document.querySelector('.kiosk-cart-items');
    const kioskTotalAmount = document.getElementById('kiosk-total-amount');
    const kioskCheckoutBtn = document.querySelector('.kiosk-checkout-btn');
    const cartCount = document.getElementById('cart-count');
    const cartButton = document.getElementById('cart-button');
    const cartPanel = document.getElementById('cart-panel');
    const mainKioskContent = document.getElementById('main-kiosk-content');
    const greetingScreen = document.getElementById('greeting-screen');
    const backToMenuBtn = document.getElementById('back-to-menu-btn');
    const filterButtons = document.querySelectorAll('.kiosk-filter-btn');
    // Normalize payment method to lowercase ('gcash' or 'cash')
    let selectedPaymentMethod = (window.KIOSK_PAYMENT_METHOD || '').toLowerCase() || null; // Variable to store the selected method
    // Dining option now comes from PHP (kiosk_dining.php -> kiosk_payment.php -> kiosk.php?dining_option=...)
    let selectedDiningOption = window.KIOSK_DINING_OPTION || 'dine_in';

    // Elements for table number step
    const tableNumberScreen = document.getElementById('table-number-screen');
    const tableNumberInput = document.getElementById('table_number_input');
    const confirmTableBtn = document.getElementById('confirm-table-btn');
    const cancelTableBtn = document.getElementById('cancel-table-btn');

    // Category filtering (similar to POS)
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            document.querySelectorAll('.kiosk-product-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });


    // Add to cart functionality
    kioskProductsGrid.addEventListener('click', function(e) {
        const targetButton = e.target.closest('.kiosk-add-to-cart');
        if (targetButton) {
            const productCard = targetButton.closest('.kiosk-product-card');
            const productId = productCard.dataset.id;
            const productName = productCard.querySelector('h3').textContent;
            const productPrice = parseFloat(productCard.dataset.price);
            // Stock check should ideally be ingredient-based now, not product stock
            // const stock = parseInt(productCard.querySelector('.kiosk-stock').textContent.split(': ')[1]);

            // Check if product is already in cart
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                 existingItem.quantity++;
                // if (existingItem.quantity < stock) {
                //     existingItem.quantity++;
                // } else {
                //     alert('Not enough stock available!');
                // }
            } else {
                 // if (stock > 0) { // Initial stock check if product stock is still used
                     cart.push({
                         id: productId,
                         name: productName,
                         price: productPrice,
                         quantity: 1
                     });
                 // } else {
                 //    alert('This item is out of stock.');
                 // }
            }
            updateKioskCart(); // Always update cart display after adding/modifying
        }
    });

    // Update cart display
    function updateKioskCart() {
        kioskCartItems.innerHTML = '';
        let total = 0;
        let totalItems = 0;

        if (cart.length === 0) {
            kioskCartItems.innerHTML = '<p>Your order is empty.</p>';
            kioskCheckoutBtn.disabled = true;
        } else {
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                totalItems += item.quantity;

                const cartItem = document.createElement('div');
                cartItem.className = 'kiosk-cart-item';
                 cartItem.innerHTML = `
                    <div class="kiosk-cart-item-details">
                        <span class="kiosk-item-name">${item.name}</span>
                        <div class="kiosk-item-quantity">
                            <button class="kiosk-quantity-btn kiosk-minus" data-id="${item.id}">-</button>
                            <span>${item.quantity}</span>
                            <button class="kiosk-quantity-btn kiosk-plus" data-id="${item.id}">+</button>
                        </div>
                        <input type="text" class="kiosk-item-instructions" data-id="${item.id}" placeholder="Special instructions (optional)" value="${item.instructions || ''}">
                    </div>
                    <div class="kiosk-cart-item-price">
                        <span>₱${itemTotal.toFixed(2)}</span>
                        <button class="kiosk-remove-item" data-id="${item.id}">×</button>
                    </div>
                `;
                kioskCartItems.appendChild(cartItem);
            });
             kioskCheckoutBtn.disabled = false;
        }

        kioskTotalAmount.textContent = total.toFixed(2);

        if (cartCount) {
            cartCount.textContent = totalItems;
        }

        // Add event listeners for instruction inputs
        kioskCartItems.querySelectorAll('.kiosk-item-instructions').forEach(input => {
            input.addEventListener('input', function() {
                const productId = this.dataset.id;
                const item = cart.find(item => item.id === productId);
                if (item) {
                    item.instructions = this.value;
                }
            });
        });
    }

     // Handle quantity changes and item removal (similar to POS)
    kioskCartItems.addEventListener('click', function(e) {
        const target = e.target;
        const productId = target.dataset.id;

        if (target.classList.contains('kiosk-remove-item')) {
            const index = cart.findIndex(item => item.id === productId);
            if (index > -1) {
                cart.splice(index, 1);
                updateKioskCart();
            }
        } else if (target.classList.contains('kiosk-quantity-btn')) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                 // Stock check removed as it's ingredient-based now
                 // const productCard = document.querySelector(`.kiosk-product-card[data-id="${productId}"]`);
                 // const stock = parseInt(productCard.querySelector('.kiosk-stock').textContent.split(': ')[1]);

                if (target.classList.contains('kiosk-plus')) {
                    item.quantity++;
                     // if (item.quantity < stock) {
                     //     item.quantity++;
                     //     updateKioskCart();
                     // } else {
                     //     alert('Not enough stock available!');
                     // }
                } else if (target.classList.contains('kiosk-minus')) {
                    if (item.quantity > 1) {
                        item.quantity--;
                        // updateKioskCart();
                    } else {
                        const index = cart.findIndex(i => i.id === productId);
                        cart.splice(index, 1);
                        // updateKioskCart();
                    }
                }
                 updateKioskCart(); // Always update cart display after quantity change
            }
        }
    });

    // Show main content and switch to dedicated cart view when cart button is clicked
    if (cartButton && cartPanel) {
        cartButton.addEventListener('click', function() {
            // If we're still on the greeting screen, switch to the main kiosk layout
            if (greetingScreen && mainKioskContent) {
                greetingScreen.style.display = 'none';
                mainKioskContent.classList.remove('hidden');
            }

            // Ensure cart is visible and take over the layout
            cartPanel.classList.remove('hidden');
            if (mainKioskContent) {
                mainKioskContent.classList.add('cart-view');
            }

            // Explicitly hide the products section so only the cart is visible
            const kioskProductsSection = document.querySelector('.kiosk-products-section');
            if (kioskProductsSection) {
                kioskProductsSection.style.display = 'none';
            }

            // Scroll to top of page (useful on smaller screens)
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Back to menu: return to product view and hide dedicated cart view
    if (backToMenuBtn && cartPanel && mainKioskContent) {
        backToMenuBtn.addEventListener('click', function() {
            cartPanel.classList.add('hidden');
            mainKioskContent.classList.remove('cart-view');

            // Show products section again
            const kioskProductsSection = document.querySelector('.kiosk-products-section');
            if (kioskProductsSection) {
                kioskProductsSection.style.display = 'block';
            }
        });
    }

    // Function to update the checkout button state
    function updateCheckoutButtonState() {
        // Checkout button is enabled if there are items in cart and payment method is selected
        kioskCheckoutBtn.disabled = cart.length === 0 || !selectedPaymentMethod;
    }

    // This script will primarily handle cart logic and sending the order,
    // the initial greeting screen transition is handled in kiosk.php.

    // Step 1: Click "Place Order" -> show table number screen
    kioskCheckoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
            alert('Your order is empty!');
            return;
        }

        if (!selectedPaymentMethod) {
            alert('Payment method is missing. Please go back and select again.');
            return;
        }

        if (tableNumberScreen) {
            // Hide cart view, show table number screen
            if (cartPanel) cartPanel.classList.add('hidden');
            if (mainKioskContent) mainKioskContent.classList.remove('cart-view');

            const kioskProductsSection = document.querySelector('.kiosk-products-section');
            if (kioskProductsSection) {
                kioskProductsSection.style.display = 'none';
            }

            tableNumberScreen.classList.remove('hidden');
            if (tableNumberInput) {
                tableNumberInput.focus();
            }
        }
    });

    // Step 2: Confirm / cancel table number
    if (cancelTableBtn && tableNumberScreen) {
        cancelTableBtn.addEventListener('click', () => {
            // Hide table number screen and return to cart view
            tableNumberScreen.classList.add('hidden');
            if (mainKioskContent) mainKioskContent.classList.add('cart-view');
            if (cartPanel) cartPanel.classList.remove('hidden');

            const kioskProductsSection = document.querySelector('.kiosk-products-section');
            if (kioskProductsSection) {
                kioskProductsSection.style.display = 'none';
            }
        });
    }

    async function submitOrder(tableNumber) {
        const paymentMethod = selectedPaymentMethod;

        const orderData = {
            items: cart,
            table_number: tableNumber,
            payment_method: paymentMethod,
            dining_option: selectedDiningOption
        };

        try {
            console.log('Sending order data:', orderData); // Debug log

            const response = await fetch('process_kiosk_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            });

            console.log('Response status:', response.status); // Debug log

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Server response:', result); // Debug log

            if (result.success) {
                console.log('Order placed successfully. Processing client-side update...');
                console.log('Selected payment method:', paymentMethod);

                // Clear cart and update display
                cart.length = 0;
                updateKioskCart();

                // Hide main content and show thank you message
                const mainContent = document.getElementById('main-kiosk-content');
                const cashThankYouMessage = document.getElementById('cash-thank-you-message');
                const gcashQrCodeMessage = document.getElementById('gcash-qr-code-message');

                if (mainContent && cashThankYouMessage && gcashQrCodeMessage) {
                    // Hide all ordering UI
                    mainContent.style.display = 'none';
                    if (tableNumberScreen) {
                        tableNumberScreen.classList.add('hidden');
                    }

                    // Show thank-you based on payment method
                    if (paymentMethod === 'cash') {
                        console.log('Showing cash thank you message.');
                        cashThankYouMessage.style.display = 'flex';
                    } else if (paymentMethod === 'gcash') {
                        console.log('Showing GCash QR code message.');
                        gcashQrCodeMessage.style.display = 'flex';
                    }

                    // Optional: Add a delay before redirecting back to kiosk.php
                    setTimeout(() => {
                        window.location.href = 'kiosk.php';
                    }, 5000);
                } else {
                    console.error('Could not find one or more message elements.');
                }
            } else {
                // Show the specific error message from the server
                console.error('Server reported order failed:', result.message);
                alert(result.message || 'Error placing order. Please try again.');
            }
        } catch (error) {
            console.error('Error during fetch:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack
            });
            alert('Error placing order: ' + error.message);
        }
    }

    if (confirmTableBtn && tableNumberScreen) {
        confirmTableBtn.addEventListener('click', async () => {
            const tableNumber = tableNumberInput ? tableNumberInput.value.trim() : '';

            if (!tableNumber) {
                alert('Please enter your table number');
                return;
            }

            await submitOrder(tableNumber);
        });
    }

    // Initial cart display
    updateKioskCart();

});
