// Hàm cập nhật số lượng sản phẩm
function updateQuantity(productId, change) {
    const quantityElement = document.getElementById(`quantity-${productId}`);
    let quantity = parseInt(quantityElement.textContent) + change;
    if (quantity < 1) quantity = 1;
    quantityElement.textContent = quantity;
}

// Hàm thêm vào danh sách yêu thích
function addToWishlist(productId) {
    fetch('add_to_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã thêm sản phẩm vào danh sách yêu thích!', 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra!', 'error');
    });
}

// Hàm xóa khỏi danh sách yêu thích
function removeFromWishlist(productId) {
    fetch('remove_from_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã xóa sản phẩm khỏi danh sách yêu thích!', 'success');
            // Refresh the page to update the wishlist
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra!', 'error');
    });
}

// Hàm thêm vào giỏ hàng
function addToCart(productId, quantity = null) {
    // If quantity is not provided or invalid, show the product details modal first
    if (quantity === null || isNaN(quantity) || quantity < 1) {
        showProductDetails(productId);
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            const modal = document.querySelector('.product-modal');
            if (modal) {
                modal.remove();
            }
        } else {
            if (data.message.includes('đăng nhập')) {
                showNotification(data.message, 'error');
                setTimeout(() => {
                    window.location.href = '../auth/auth.php';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
    });
}

// Show product details
function showProductDetails(productId) {
    // First check if product is in wishlist
    fetch(`check_wishlist.php?product_id=${productId}`)
    .then(response => response.json())
    .then(wishlistData => {
        // Then get product details
        return fetch(`get_product_details.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showNotification(data.error || 'Có lỗi xảy ra khi tải thông tin sản phẩm', 'error');
                    return;
                }
                const product = data.data;
                const modal = document.createElement('div');
                modal.className = 'product-modal';
                modal.innerHTML = createProductDetailsContent(product, wishlistData.in_wishlist);
                
                // Remove any existing modals
                const existingModal = document.querySelector('.product-modal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                document.body.appendChild(modal);

                // Wait for modal to be added to DOM
                setTimeout(() => {
                    // Initialize quantity controls
                    initializeQuantityControls(product.quantity);

                    // Add click handlers for add to cart and buy now buttons
                    const addToCartBtn = modal.querySelector('.add-to-cart-btn');
                    const buyNowBtn = modal.querySelector('.buy-now-btn');
                    const quantityElement = modal.querySelector('#quantity');

                    if (addToCartBtn && quantityElement) {
                        addToCartBtn.onclick = function(e) {
                            e.stopPropagation();
                            const quantity = parseInt(quantityElement.textContent);
                            const maxQuantity = parseInt(quantityElement.dataset.maxQuantity);
                            
                            if (quantity > maxQuantity) {
                                showNotification('Số lượng vượt quá tồn kho', 'error');
                                return;
                            }
                            addToCart(product.product_id, quantity);
                        };
                    }

                    if (buyNowBtn && quantityElement) {
                        buyNowBtn.onclick = function(e) {
                            e.stopPropagation();
                            const quantity = parseInt(quantityElement.textContent);
                            const maxQuantity = parseInt(quantityElement.dataset.maxQuantity);
                            
                            if (quantity > maxQuantity) {
                                showNotification('Số lượng vượt quá tồn kho', 'error');
                                return;
                            }
                            buyNow(product.product_id, quantity);
                        };
                    }

                    // Close modal when clicking outside
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            this.remove();
                        }
                    });
                }, 0);
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra khi tải thông tin sản phẩm', 'error');
            });
    });
}

// Toggle wishlist
function toggleWishlist(productId, button) {
    if (!button.classList.contains('active')) {
        // Add to wishlist
        addToWishlist(productId);
        button.classList.add('active');
    } else {
        // Remove from wishlist
        removeFromWishlist(productId);
        button.classList.remove('active');
    }
}

// Load related products
function loadRelatedProducts(productId, subcategoryId) {
    fetch(`get_related_products.php?subcategory_id=${subcategoryId}&exclude_id=${productId}`)
        .then(response => response.json())
        .then(response => {
            if (!response.success) {
                throw new Error(response.error || 'Có lỗi xảy ra khi tải sản phẩm liên quan');
            }

            const container = document.getElementById(`related-products-${productId}`);
            if (!response.data.length) {
                container.innerHTML = '<p>Không có sản phẩm liên quan.</p>';
                return;
            }

            container.innerHTML = response.data.map(product => `
                <div class="related-product-card" onclick="showProductDetails(${product.product_id})">
                    <img class="related-product-image" src="${product.image_url}" alt="${product.name}">
                    <div class="related-product-info">
                        <h4 class="related-product-title">${product.name}</h4>
                        <div class="related-product-price">${product.formatted_price}đ</div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            const container = document.getElementById(`related-products-${productId}`);
            container.innerHTML = '<p class="error">Không thể tải sản phẩm liên quan.</p>';
        });
}

// Close product details modal
function closeProductDetails() {
    const modal = document.querySelector('.product-details-modal');
    const overlay = document.querySelector('.modal-overlay');
    
    if (modal && overlay) {
        modal.classList.remove('active');
        overlay.classList.remove('active');
        
        setTimeout(() => {
            modal.remove();
            overlay.remove();
        }, 300);
    }
}

// Hàm mua ngay
function buyNow(productId, quantity = null) {
    // If quantity is not provided or invalid, show the product details modal first
    if (quantity === null || isNaN(quantity) || quantity < 1) {
        showProductDetails(productId);
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('buy_now', 'true');

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'thanh-toan.php';
        } else {
            if (data.message.includes('đăng nhập')) {
                showNotification(data.message, 'error');
                setTimeout(() => {
                    window.location.href = '../auth/auth.php';
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi xử lý mua hàng', 'error');
    });
}

// Ngăn chặn sự kiện click lan truyền từ các nút trong product-details
document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelectorAll('.product-actions button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Show/hide subcategories in product page
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        const link = item.querySelector('a');
        const subcategoryList = item.querySelector('.subcategory-list');
        
        if (subcategoryList) {
            item.addEventListener('mouseenter', () => {
                subcategoryList.style.display = 'block';
            });
            
            item.addEventListener('mouseleave', () => {
                subcategoryList.style.display = 'none';
            });
        }
    });
});

// Function to toggle subcategories
function toggleSubcategories(event) {
    event.preventDefault();
    const categoryItem = event.currentTarget.parentElement;
    const wasActive = categoryItem.classList.contains('active');
    
    // Remove active class from all categories
    document.querySelectorAll('.category-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Toggle active class on clicked category
    if (!wasActive) {
        categoryItem.classList.add('active');
    }
}

// Function to toggle categories visibility
function toggleCategories() {
    const hiddenCategories = document.querySelectorAll('.hidden-category');
    const showMoreBtn = document.querySelector('.show-more-btn');
    
    if (!hiddenCategories.length || !showMoreBtn) {
        return;
    }
    
    const isExpanded = showMoreBtn.textContent.includes('Thu gọn');
    
    hiddenCategories.forEach(category => {
        category.style.display = isExpanded ? 'none' : 'block';
    });
    
    showMoreBtn.textContent = isExpanded ? 'Xem thêm' : 'Thu gọn';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set up category click handlers
    const categoryLinks = document.querySelectorAll('.category-item > a');
    categoryLinks.forEach(link => {
        if (link.nextElementSibling && link.nextElementSibling.classList.contains('subcategory-list')) {
            link.addEventListener('click', toggleSubcategories);
        }
    });
    
    // Set up show more button
    const showMoreBtn = document.querySelector('.show-more-btn');
    if (showMoreBtn) {
        // Hide categories initially
        const hiddenCategories = document.querySelectorAll('.hidden-category');
        hiddenCategories.forEach(category => {
            category.style.display = 'none';
        });
        
        // Add click event
        showMoreBtn.addEventListener('click', toggleCategories);
    }
    
    // Check URL parameters to activate correct category
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');
    if (categoryId) {
        const activeCategory = document.querySelector(`.category-item[data-id="${categoryId}"]`);
        if (activeCategory) {
            activeCategory.classList.add('active');
        }
    }
});

// Remove duplicate event listener
const oldCategoryListener = document.querySelector('.show-more');
if (oldCategoryListener) {
    oldCategoryListener.removeEventListener('click', toggleCategories);
}

// Add event listener for category items
document.addEventListener('DOMContentLoaded', function() {
    const categoryItems = document.querySelectorAll('.category-item');
    const showMoreBtn = document.querySelector('.show-more');

    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', toggleCategories);
    }

    categoryItems.forEach(item => {
        const subcategoryList = item.querySelector('.subcategory-list');
        if (subcategoryList) {
            item.addEventListener('mouseenter', () => {
                subcategoryList.style.display = 'block';
            });
            
            item.addEventListener('mouseleave', () => {
                subcategoryList.style.display = 'none';
            });
        }
    });
});

function showNotification(message, type = 'success') {
    // Remove any existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        ${message}
        <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;

    // Add to document
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        if (notification && notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Slide Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const slideMenu = document.querySelector('.slide-menu');
    const closeBtn = document.querySelector('.slide-menu-close');
    const menuOverlay = document.querySelector('.menu-overlay');

    // Nếu không tìm thấy slide menu, không cần thực hiện các chức năng liên quan
    if (!slideMenu) return;

    function openMenu() {
        slideMenu.classList.add('active');
        menuOverlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        slideMenu.classList.remove('active');
        menuOverlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Thêm event listeners chỉ khi các elements tồn tại
    if (menuToggle) {
        menuToggle.addEventListener('click', openMenu);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeMenu);
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeMenu);
    }

    // Close menu when clicking a link (for mobile)
    const menuLinks = slideMenu.querySelectorAll('a');
    if (menuLinks.length > 0) {
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeMenu();
                }
            });
        });
    }

    // Handle escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && slideMenu.classList.contains('active')) {
            closeMenu();
        }
    });
});

// Function to create product details modal content
function createProductDetailsContent(product, isInWishlist) {
    return `
        <div class="modal-content">
            <button class="close-button" onclick="this.closest('.product-modal').remove()">&times;</button>
            <div class="product-details">
                <div class="product-image-container">
                    <div class="product-image">
                        <img src="${product.image_url}" alt="${product.name}">
                        <button class="product-wishlist-btn ${isInWishlist ? 'active' : ''}" onclick="event.stopPropagation(); toggleWishlist(${product.product_id}, this)">
                            ${isInWishlist ? '❤' : '♡'}
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h2>${product.name}</h2>
                    <p class="price">${product.formatted_price}</p>
                    <p class="description">${product.description}</p>
                    <div class="stock-status ${product.stock_class}">
                        ${product.stock_status}
                    </div>
                    <div class="quantity-controls">
                        <button onclick="event.stopPropagation(); decrementQuantity()" ${product.quantity <= 0 ? 'disabled' : ''}>-</button>
                        <span id="quantity" data-max-quantity="${product.quantity}">1</span>
                        <button onclick="event.stopPropagation(); incrementQuantity()" ${product.quantity <= 0 ? 'disabled' : ''}>+</button>
                    </div>
                    <div class="action-buttons">
                        <button class="add-to-cart-btn" ${product.quantity <= 0 ? 'disabled' : ''}>
                            🛒 Thêm vào giỏ
                        </button>
                        <button class="buy-now-btn" ${product.quantity <= 0 ? 'disabled' : ''}>
                            ⚡ Mua ngay
                        </button>
                    </div>
                    <div class="product-meta">
                        <p><i class="fas fa-box"></i> Danh mục: ${product.category_name}</p>
                        <p><i class="fas fa-tag"></i> Loại: ${product.subcategory_name}</p>
                        <p><i class="fas fa-calendar"></i> Ngày đăng: ${product.formatted_date}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Initialize quantity controls
function initializeQuantityControls(maxQuantity) {
    const quantityElement = document.getElementById('quantity');
    const decrementBtn = document.querySelector('.quantity-controls button:first-child');
    const incrementBtn = document.querySelector('.quantity-controls button:last-child');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const buyNowBtn = document.querySelector('.buy-now-btn');

    if (!quantityElement || !decrementBtn || !incrementBtn) return;

    // Set initial state
    updateQuantityButtons(1, maxQuantity);

    // Add event listeners
    decrementBtn.onclick = function(e) {
        e.stopPropagation();
        decrementQuantity();
    };

    incrementBtn.onclick = function(e) {
        e.stopPropagation();
        incrementQuantity();
    };

    if (addToCartBtn) {
        addToCartBtn.disabled = maxQuantity <= 0;
    }
    if (buyNowBtn) {
        buyNowBtn.disabled = maxQuantity <= 0;
    }
}

// Hàm tăng số lượng
function incrementQuantity() {
    const quantityElement = document.getElementById('quantity');
    if (!quantityElement) return;
    
    const maxQuantity = parseInt(quantityElement.dataset.maxQuantity);
    let currentQuantity = parseInt(quantityElement.textContent);
    
    if (currentQuantity < maxQuantity) {
        currentQuantity++;
        quantityElement.textContent = currentQuantity;
        updateQuantityButtons(currentQuantity, maxQuantity);
    }
}

// Hàm giảm số lượng
function decrementQuantity() {
    const quantityElement = document.getElementById('quantity');
    if (!quantityElement) return;
    
    const maxQuantity = parseInt(quantityElement.dataset.maxQuantity);
    let currentQuantity = parseInt(quantityElement.textContent);
    
    if (currentQuantity > 1) {
        currentQuantity--;
        quantityElement.textContent = currentQuantity;
        updateQuantityButtons(currentQuantity, maxQuantity);
    }
}

// Hàm cập nhật trạng thái nút
function updateQuantityButtons(currentQuantity, maxQuantity) {
    const decrementBtn = document.querySelector('.quantity-controls button:first-child');
    const incrementBtn = document.querySelector('.quantity-controls button:last-child');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const buyNowBtn = document.querySelector('.buy-now-btn');
    
    if (decrementBtn) {
        decrementBtn.disabled = currentQuantity <= 1;
    }
    if (incrementBtn) {
        incrementBtn.disabled = currentQuantity >= maxQuantity;
    }
    if (addToCartBtn && buyNowBtn) {
        const disabled = currentQuantity > maxQuantity || currentQuantity < 1;
        addToCartBtn.disabled = disabled;
        buyNowBtn.disabled = disabled;
    }
}

// Hàm cập nhật số lượng giỏ hàng trên UI
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

// Slider functionality
function initializeSlider() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (!slider || !slides.length || !dots.length || !prevBtn || !nextBtn) {
        return; // Exit if elements don't exist
    }

    let currentSlide = 0;
    let slideInterval;
    let isTransitioning = false;

    // Update slider position and dots
    function updateSlider() {
        if (isTransitioning) return;
        
        isTransitioning = true;
        slider.style.transform = `translateX(-${currentSlide * 20}%)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });

        // Reset transition lock after animation completes
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    }

    // Start automatic sliding
    function startSlideTimer() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
        slideInterval = setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            updateSlider();
        }, 5000);
    }

    // Go to next slide
    function nextSlide() {
        if (isTransitioning) return;
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
        startSlideTimer(); // Reset timer
    }

    // Go to previous slide
    function prevSlide() {
        if (isTransitioning) return;
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlider();
        startSlideTimer(); // Reset timer
    }

    // Event listeners
    prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        prevSlide();
    });

    nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        nextSlide();
    });

    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            if (isTransitioning || currentSlide === index) return;
            currentSlide = index;
            updateSlider();
            startSlideTimer(); // Reset timer
        });
    });

    // Pause on hover
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });

    slider.addEventListener('mouseleave', () => {
        startSlideTimer();
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        }
    });

    // Touch events for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const swipeThreshold = 50; // minimum distance for swipe
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextSlide(); // Swipe left
            } else {
                prevSlide(); // Swipe right
            }
        }
    }

    // Initialize
    updateSlider();
    startSlideTimer();
}

// Initialize slider when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSlider();
}); 