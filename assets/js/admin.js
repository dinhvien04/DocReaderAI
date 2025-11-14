/**
 * Admin JavaScript
 * Handles admin operations: user management, system config, statistics
 */

/**
 * Get all users with pagination and search
 * @param {number} page - Page number
 * @param {string} search - Search term
 * @returns {Promise<object>} Users data
 */
async function getUsers(page = 1, search = '') {
    try {
        const response = await apiRequest(
            `${API_BASE}/admin.php?action=users&page=${page}&limit=20&search=${encodeURIComponent(search)}`
        );

        return response.data;
    } catch (error) {
        console.error('Get users error:', error);
        throw error;
    }
}

/**
 * Update user role
 * @param {number} userId - User ID
 * @param {string} role - New role (user, admin)
 * @returns {Promise<boolean>} Success status
 */
async function updateUserRole(userId, role) {
    try {
        const response = await apiRequest(`${API_BASE}/admin.php?action=update-role`, {
            method: 'POST',
            body: JSON.stringify({ userId, role })
        });

        if (response.success) {
            showToast('Đã cập nhật role', 'success');
            await loadUsers();
        }

        return response.success;
    } catch (error) {
        console.error('Update role error:', error);
        throw error;
    }
}

/**
 * Delete user
 * @param {number} userId - User ID
 * @returns {Promise<boolean>} Success status
 */
async function deleteUser(userId) {
    if (!confirm('Bạn có chắc muốn xóa user này? Tất cả dữ liệu của user sẽ bị xóa.')) {
        return false;
    }

    try {
        const response = await apiRequest(`${API_BASE}/admin.php?action=delete-user`, {
            method: 'DELETE',
            body: JSON.stringify({ userId })
        });

        if (response.success) {
            showToast('Đã xóa user', 'success');
            await loadUsers();
        }

        return response.success;
    } catch (error) {
        console.error('Delete user error:', error);
        throw error;
    }
}

/**
 * Get system statistics
 * @returns {Promise<object>} Statistics data
 */
async function getStats() {
    try {
        const response = await apiRequest(`${API_BASE}/admin.php?action=stats`);
        return response.data;
    } catch (error) {
        console.error('Get stats error:', error);
        throw error;
    }
}

/**
 * Update system config
 * @param {string} key - Config key
 * @param {string} value - Config value
 * @returns {Promise<boolean>} Success status
 */
async function updateConfig(key, value) {
    try {
        const response = await apiRequest(`${API_BASE}/admin.php?action=update-config`, {
            method: 'POST',
            body: JSON.stringify({ key, value })
        });

        if (response.success) {
            showToast('Đã cập nhật config', 'success');
        }

        return response.success;
    } catch (error) {
        console.error('Update config error:', error);
        throw error;
    }
}

/**
 * Get all system configs
 * @returns {Promise<object>} Configs data
 */
async function getConfigs() {
    try {
        const response = await apiRequest(`${API_BASE}/admin.php?action=get-configs`);
        return response.data.configs;
    } catch (error) {
        console.error('Get configs error:', error);
        throw error;
    }
}

/**
 * Render user table
 * @param {array} users - List of users
 */
function renderUserTable(users) {
    const tbody = document.getElementById('users-tbody');
    if (!tbody) return;

    if (!users || users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-8 text-gray-500">
                    Không có user nào
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = '';

    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';

        const statusBadge = user.status === 'active' 
            ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Active</span>'
            : '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Inactive</span>';

        tr.innerHTML = `
            <td class="px-4 py-3">${user.id}</td>
            <td class="px-4 py-3">${user.email}</td>
            <td class="px-4 py-3">
                <select 
                    onchange="updateUserRole(${user.id}, this.value)" 
                    class="border rounded px-2 py-1 text-sm"
                >
                    <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                </select>
            </td>
            <td class="px-4 py-3">${statusBadge}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${formatDate(user.created_at)}</td>
            <td class="px-4 py-3">
                <button 
                    onclick="deleteUser(${user.id})" 
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition"
                >
                    Xóa
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

/**
 * Load and display users
 */
async function loadUsers() {
    const tbody = document.getElementById('users-tbody');
    if (!tbody) return;

    try {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8"><div class="spinner mx-auto"></div><p class="mt-2">Đang tải...</p></td></tr>';
        
        const searchInput = document.getElementById('user-search');
        const search = searchInput ? searchInput.value : '';
        
        const data = await getUsers(1, search);
        renderUserTable(data.users);

        // Render pagination if needed
        if (data.pages > 1) {
            renderUserPagination(data.current_page, data.pages);
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-500">Không thể tải danh sách user</td></tr>';
    }
}

/**
 * Render user pagination
 * @param {number} currentPage - Current page
 * @param {number} totalPages - Total pages
 */
function renderUserPagination(currentPage, totalPages) {
    const paginationContainer = document.getElementById('user-pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.className = i === currentPage 
            ? 'px-4 py-2 bg-purple-600 text-white rounded'
            : 'px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded';
        
        button.addEventListener('click', async function() {
            const data = await getUsers(i);
            renderUserTable(data.users);
            renderUserPagination(i, totalPages);
        });

        paginationContainer.appendChild(button);
    }
}

/**
 * Render charts using Chart.js
 * @param {object} data - Statistics data
 */
function renderCharts(data) {
    // User growth chart
    const userGrowthCanvas = document.getElementById('userGrowthChart');
    if (userGrowthCanvas) {
        new Chart(userGrowthCanvas, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: [{
                    label: 'Số người dùng',
                    data: data.userCounts,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Conversion trends chart
    const conversionCanvas = document.getElementById('conversionChart');
    if (conversionCanvas) {
        new Chart(conversionCanvas, {
            type: 'bar',
            data: {
                labels: data.dates,
                datasets: [{
                    label: 'Số lượng chuyển đổi',
                    data: data.conversionCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}

/**
 * Load and display statistics
 */
async function loadStats() {
    try {
        const data = await getStats();

        // Update stat cards
        const totalUsersEl = document.getElementById('total-users');
        const activeUsersEl = document.getElementById('active-users');
        const totalConversionsEl = document.getElementById('total-conversions');

        if (totalUsersEl) totalUsersEl.textContent = data.total_users;
        if (activeUsersEl) activeUsersEl.textContent = data.active_users;
        if (totalConversionsEl) totalConversionsEl.textContent = data.total_conversions;

        // Render charts
        renderCharts(data);
    } catch (error) {
        console.error('Load stats error:', error);
        showToast('Không thể tải thống kê', 'error');
    }
}

/**
 * Load and display system configs
 */
async function loadConfigs() {
    const container = document.getElementById('configs-container');
    if (!container) return;

    try {
        container.innerHTML = '<div class="text-center py-8"><div class="spinner mx-auto"></div><p class="mt-2">Đang tải...</p></div>';
        
        const configs = await getConfigs();

        container.innerHTML = '';

        // Group configs by category
        Object.keys(configs).forEach(category => {
            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'mb-8';

            const categoryTitle = category.charAt(0).toUpperCase() + category.slice(1);
            categoryDiv.innerHTML = `<h3 class="text-xl font-bold mb-4 text-purple-600">${categoryTitle}</h3>`;

            const configList = document.createElement('div');
            configList.className = 'space-y-4';

            configs[category].forEach(config => {
                const configDiv = document.createElement('div');
                configDiv.className = 'bg-white p-4 rounded-lg shadow';

                const isMasked = config.config_key.includes('key') || config.config_key.includes('password');

                configDiv.innerHTML = `
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <label class="font-medium text-gray-700">${config.config_key}</label>
                            <p class="text-sm text-gray-500 mt-1">${config.description || ''}</p>
                        </div>
                        <div class="flex gap-2 items-center">
                            <input 
                                type="${isMasked ? 'password' : 'text'}" 
                                value="${config.config_value}" 
                                id="config-${config.config_key}"
                                class="border rounded px-3 py-2 w-64"
                            />
                            <button 
                                onclick="updateConfig('${config.config_key}', document.getElementById('config-${config.config_key}').value)"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded transition"
                            >
                                Lưu
                            </button>
                        </div>
                    </div>
                `;

                configList.appendChild(configDiv);
            });

            categoryDiv.appendChild(configList);
            container.appendChild(categoryDiv);
        });
    } catch (error) {
        container.innerHTML = '<div class="text-center py-8 text-red-500">Không thể tải cấu hình</div>';
    }
}

/**
 * Handle user search
 */
function handleUserSearch() {
    const searchInput = document.getElementById('user-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(function() {
        loadUsers();
    }, 500));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load users if on user management page
    if (document.getElementById('users-tbody')) {
        loadUsers();
        handleUserSearch();
    }

    // Load stats if on admin dashboard
    if (document.getElementById('userGrowthChart')) {
        loadStats();
    }

    // Load configs if on config page
    if (document.getElementById('configs-container')) {
        loadConfigs();
    }
});
