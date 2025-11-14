/**
 * Core Application JavaScript
 * Global configuration and utility functions
 */

// Global configuration
const API_BASE = '/KK/api';

/**
 * Show toast notification
 * @param {string} message - Message to display
 * @param {string} type - Type of notification (success, error, info)
 */
function showToast(message, type = 'success') {
    const backgroundColor = {
        'success': 'linear-gradient(to right, #10b981, #059669)',
        'error': 'linear-gradient(to right, #ef4444, #dc2626)',
        'info': 'linear-gradient(to right, #3b82f6, #2563eb)',
        'warning': 'linear-gradient(to right, #f59e0b, #d97706)'
    };

    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
        style: {
            background: backgroundColor[type] || backgroundColor['success'],
        }
    }).showToast();
}

/**
 * API request wrapper with error handling
 * @param {string} url - API endpoint URL
 * @param {object} options - Fetch options
 * @returns {Promise<object>} Response data
 */
async function apiRequest(url, options = {}) {
    try {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        };

        const response = await fetch(url, {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }

        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

/**
 * Set loading state for button
 * @param {HTMLElement} element - Button element
 * @param {boolean} loading - Loading state
 */
function setLoading(element, loading) {
    if (!element) return;

    if (loading) {
        // Only save original text if not already saved
        if (!element.dataset.originalText) {
            element.dataset.originalText = element.innerHTML;
        }
        element.disabled = true;
        element.innerHTML = '<span class="spinner"></span> Đang xử lý...';
    } else {
        element.disabled = false;
        // Restore original text and clear the saved value
        if (element.dataset.originalText) {
            element.innerHTML = element.dataset.originalText;
            delete element.dataset.originalText;
        }
    }
}

/**
 * Format date to Vietnamese format
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

/**
 * Validate email format
 * @param {string} email - Email address
 * @returns {boolean} Is valid
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Đã copy vào clipboard', 'success');
    } catch (error) {
        console.error('Copy failed:', error);
        showToast('Không thể copy', 'error');
    }
}

/**
 * Get query parameter from URL
 * @param {string} param - Parameter name
 * @returns {string|null} Parameter value
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Show/hide element
 * @param {HTMLElement} element - Element to toggle
 * @param {boolean} show - Show or hide
 */
function toggleElement(element, show) {
    if (!element) return;
    
    if (show) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}

// Initialize app on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Check for error messages in URL
    const error = getQueryParam('error');
    if (error) {
        const errorMessages = {
            'session_expired': 'Phiên đăng nhập đã hết hạn',
            'unauthorized': 'Bạn không có quyền truy cập',
            'invalid_credentials': 'Email hoặc mật khẩu không đúng'
        };
        showToast(errorMessages[error] || 'Có lỗi xảy ra', 'error');
    }

    // Check for success messages
    const success = getQueryParam('success');
    if (success) {
        const successMessages = {
            'registered': 'Đăng ký thành công',
            'password_reset': 'Mật khẩu đã được đặt lại'
        };
        showToast(successMessages[success] || 'Thành công', 'success');
    }
});
