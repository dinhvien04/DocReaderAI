/**
 * Authentication JavaScript
 * Handles login, register, logout, OTP operations
 */

/**
 * Login user with username or email
 * @param {string} identifier - Username or email
 * @param {string} password - User password
 * @returns {Promise<object>} Response data
 */
async function login(identifier, password) {
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=login`, {
            method: 'POST',
            body: JSON.stringify({ identifier, password })
        });

        if (response.success) {
            storeSession(response.data.user);
            showToast('Đăng nhập thành công', 'success');
            
            // Redirect based on user role
            const user = response.data.user;
            console.log('User data:', user);
            console.log('User role:', user.role);
            
            let redirectUrl;
            
            if (user.role === 'admin') {
                // Admin goes to admin panel
                redirectUrl = `${API_BASE.replace('/api', '')}/views/admin`;
                console.log('Admin detected, redirecting to:', redirectUrl);
            } else {
                // Regular user goes to dashboard
                redirectUrl = `${API_BASE.replace('/api', '')}/dashboard`;
                console.log('Regular user, redirecting to:', redirectUrl);
            }
            
            // Check if there's a custom redirect parameter
            const customRedirect = getQueryParam('redirect');
            if (customRedirect) {
                redirectUrl = customRedirect;
                console.log('Custom redirect:', redirectUrl);
            }
            
            console.log('Final redirect URL:', redirectUrl);
            
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 500);
        }

        return response;
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

/**
 * Register new user - New flow
 * @param {object} data - Registration data {username, email, password}
 * @returns {Promise<object>} Response data
 */
async function register(data) {
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=register`, {
            method: 'POST',
            body: JSON.stringify(data)
        });

        if (response.success) {
            showToast('OTP đã được gửi đến email của bạn', 'success');
        }

        return response;
    } catch (error) {
        console.error('Register error:', error);
        throw error;
    }
}

/**
 * Verify OTP and activate account
 * @param {string} email - User email
 * @param {string} otp - OTP code
 * @returns {Promise<boolean>} Is valid
 */
async function verifyOtp(email, otp) {
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=verify-otp`, {
            method: 'POST',
            body: JSON.stringify({ email, otp })
        });

        if (response.success) {
            showToast('Tài khoản đã được kích hoạt thành công!', 'success');
            setTimeout(() => {
                window.location.href = `${API_BASE.replace('/api', '')}/login?success=registered`;
            }, 1000);
        }

        return response.success;
    } catch (error) {
        console.error('Verify OTP error:', error);
        return false;
    }
}

/**
 * Reset password - Send OTP
 * @param {string} email - User email
 * @returns {Promise<object>} Response data
 */
async function sendResetOtp(email) {
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=reset-password`, {
            method: 'POST',
            body: JSON.stringify({ step: 'send-otp', email })
        });

        if (response.success) {
            showToast('OTP đã được gửi đến email của bạn', 'success');
        }

        return response;
    } catch (error) {
        console.error('Send reset OTP error:', error);
        throw error;
    }
}

/**
 * Reset password - Verify OTP and update password
 * @param {string} email - User email
 * @param {string} otp - OTP code
 * @param {string} password - New password
 * @returns {Promise<object>} Response data
 */
async function resetPassword(email, otp, password) {
    try {
        const response = await apiRequest(`${API_BASE}/auth.php?action=reset-password`, {
            method: 'POST',
            body: JSON.stringify({ step: 'reset', email, otp, password })
        });

        if (response.success) {
            showToast('Mật khẩu đã được đặt lại thành công', 'success');
            setTimeout(() => {
                window.location.href = `${API_BASE.replace('/api', '')}/login?success=password_reset`;
            }, 1000);
        }

        return response;
    } catch (error) {
        console.error('Reset password error:', error);
        throw error;
    }
}

/**
 * Logout user
 */
async function logout() {
    try {
        await apiRequest(`${API_BASE}/auth.php?action=logout`, {
            method: 'POST'
        });

        clearSession();
        showToast('Đăng xuất thành công', 'success');
        
        setTimeout(() => {
            window.location.href = API_BASE.replace('/api', '') || '/KK';
        }, 500);
    } catch (error) {
        console.error('Logout error:', error);
        // Force logout even if API fails
        clearSession();
        window.location.href = API_BASE.replace('/api', '') || '/KK';
    }
}

/**
 * Store user session in sessionStorage
 * @param {object} userData - User data
 */
function storeSession(userData) {
    try {
        sessionStorage.setItem('user', JSON.stringify(userData));
    } catch (error) {
        console.error('Store session error:', error);
    }
}

/**
 * Get user session from sessionStorage
 * @returns {object|null} User data
 */
function getSession() {
    try {
        const user = sessionStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    } catch (error) {
        console.error('Get session error:', error);
        return null;
    }
}

/**
 * Clear user session
 */
function clearSession() {
    try {
        sessionStorage.removeItem('user');
    } catch (error) {
        console.error('Clear session error:', error);
    }
}

/**
 * Check if user is logged in
 * @returns {boolean} Is logged in
 */
function isLoggedIn() {
    return getSession() !== null;
}

/**
 * Handle login form submission
 */
function handleLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const identifier = document.getElementById('identifier').value.trim();
        const password = document.getElementById('password').value;
        const submitBtn = form.querySelector('button[type="submit"]');

        // Validate
        if (!identifier || !password) {
            showToast('Vui lòng điền đầy đủ thông tin', 'error');
            return;
        }

        // Submit
        setLoading(submitBtn, true);
        try {
            await login(identifier, password);
        } catch (error) {
            // Error already handled by apiRequest
        } finally {
            setLoading(submitBtn, false);
        }
    });
}

/**
 * Handle registration form - New flow
 */
function handleRegisterForm() {
    let registrationData = {
        username: '',
        email: '',
        password: ''
    };

    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const registerForm = document.getElementById('register-form');
    const verifyForm = document.getElementById('verify-form');
    const backBtn = document.getElementById('back-btn');

    // Step 1: Register with all info
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const registerBtn = document.getElementById('register-btn');

            // Validate
            if (!username || !email || !password || !confirmPassword) {
                showToast('Vui lòng điền đầy đủ thông tin', 'error');
                return;
            }

            // Validate username format
            if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
                showToast('Username phải từ 3-20 ký tự, chỉ chứa chữ, số và dấu gạch dưới', 'error');
                return;
            }

            if (!isValidEmail(email)) {
                showToast('Email không hợp lệ', 'error');
                return;
            }

            if (password.length < 6) {
                showToast('Mật khẩu phải có ít nhất 6 ký tự', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showToast('Mật khẩu không khớp', 'error');
                return;
            }

            // Store data
            registrationData = { username, email, password };

            // Submit
            setLoading(registerBtn, true);
            try {
                await register(registrationData);
                // Move to step 2
                toggleElement(step1, false);
                toggleElement(step2, true);
            } catch (error) {
                // Error handled
            } finally {
                setLoading(registerBtn, false);
            }
        });
    }

    // Step 2: Verify OTP
    if (verifyForm) {
        verifyForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const otp = document.getElementById('otp').value.trim();
            const verifyBtn = document.getElementById('verify-btn');

            if (!otp || otp.length !== 6) {
                showToast('OTP phải có 6 chữ số', 'error');
                return;
            }

            setLoading(verifyBtn, true);
            try {
                await verifyOtp(registrationData.email, otp);
            } catch (error) {
                // Error handled
            } finally {
                setLoading(verifyBtn, false);
            }
        });
    }

    // Back button
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            toggleElement(step2, false);
            toggleElement(step1, true);
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleLoginForm();
    handleRegisterForm();
});
