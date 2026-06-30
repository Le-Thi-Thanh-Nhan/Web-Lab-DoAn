// Toggle sidebar
document.getElementById('sidebar-toggle').addEventListener('click', function() {
    document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
});

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Modal functions
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Handle form submission
function handleSubmit(event, url) {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal(event.target.closest('.modal').id);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    });
}

// Delete confirmation
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Image preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle recipient select in notifications
function toggleRecipientSelect() {
    const recipientType = document.getElementById('recipient_type').value;
    document.getElementById('customerSelect').style.display = 'none';
    document.getElementById('adminSelect').style.display = 'none';
    
    if (recipientType === 'customer') {
        document.getElementById('customerSelect').style.display = 'block';
    } else if (recipientType === 'admin') {
        document.getElementById('adminSelect').style.display = 'block';
    }
} 