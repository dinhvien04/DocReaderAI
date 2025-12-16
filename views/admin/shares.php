<?php
$pageTitle = 'Quản lý chia sẻ - Admin';
require_once __DIR__ . '/../../middleware/admin.php';
require_once __DIR__ . '/../../includes/header.php';
?>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/vie.png">

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📤 Quản lý chia sẻ công khai</h1>
        <a href="<?= BASE_URL ?>/admin" class="text-blue-600 hover:text-blue-800">← Quay lại Dashboard</a>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-6">
        <button onclick="filterShares('all')" id="filter-all" class="px-4 py-2 rounded-lg bg-blue-500 text-white font-medium transition">
            Tất cả
        </button>
        <button onclick="filterShares('pending')" id="filter-pending" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
            ⏳ Chờ duyệt
        </button>
        <button onclick="filterShares('approved')" id="filter-approved" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
            ✅ Đã duyệt
        </button>
        <button onclick="filterShares('rejected')" id="filter-rejected" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
            ❌ Từ chối
        </button>
    </div>

    <!-- Shares List -->
    <div id="shares-container" class="space-y-4">
        <div class="text-center py-12">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải...</p>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Từ chối yêu cầu</h3>
        <textarea id="reject-note" class="w-full border border-gray-300 rounded-lg p-3 mb-4" rows="3" placeholder="Lý do từ chối (tùy chọn)..."></textarea>
        <div class="flex justify-end gap-3">
            <button onclick="closeRejectModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Hủy</button>
            <button onclick="confirmReject()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Từ chối</button>
        </div>
    </div>
</div>

<!-- Audio Preview Modal -->
<div id="audio-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 id="audio-modal-title" class="text-xl font-bold text-gray-900">Nghe thử</h3>
            <button onclick="closeAudioModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="audio-modal-text" class="bg-gray-50 p-4 rounded-lg mb-4 max-h-48 overflow-y-auto text-sm text-gray-700"></div>
        <audio id="audio-modal-player" controls class="w-full"></audio>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>/api';
let currentFilter = 'all';
let rejectingId = null;

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(t => t.remove());
    
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    toast.className = `toast-notification fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

async function loadShares(status = 'all') {
    const container = document.getElementById('shares-container');
    container.innerHTML = `
        <div class="text-center py-12">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-500">Đang tải...</p>
        </div>
    `;

    try {
        const response = await fetch(`${API_BASE}/share.php?action=admin-list&status=${status}`);
        const data = await response.json();

        if (data.success && data.data.items) {
            if (data.data.items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <div class="text-6xl mb-4">📭</div>
                        <p class="text-gray-500">Không có yêu cầu nào</p>
                    </div>
                `;
            } else {
                container.innerHTML = data.data.items.map(renderShareItem).join('');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="text-center py-12 bg-white rounded-lg shadow">
                <p class="text-red-500">⚠️ Không thể tải dữ liệu</p>
            </div>
        `;
    }
}

function renderShareItem(item) {
    const statusBadge = {
        'pending': '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">⏳ Chờ duyệt</span>',
        'approved': '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">✅ Đã duyệt</span>',
        'rejected': '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">❌ Từ chối</span>'
    };

    const truncatedText = item.text.length > 100 ? item.text.substring(0, 100) + '...' : item.text;
    const createdAt = new Date(item.created_at).toLocaleString('vi-VN');

    let actions = '';
    if (item.status === 'pending') {
        actions = `
            <button onclick="approveShare(${item.id})" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                ✅ Duyệt
            </button>
            <button onclick="openRejectModal(${item.id})" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                ❌ Từ chối
            </button>
        `;
    }

    return `
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-bold text-gray-900">${escapeHtml(item.title)}</h3>
                        ${statusBadge[item.status]}
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-2">
                        <span>👤 ${escapeHtml(item.user_email)}</span>
                        <span>📁 ${escapeHtml(item.category_name)}</span>
                        <span>📅 ${createdAt}</span>
                    </div>
                    ${item.description ? `<p class="text-gray-600 text-sm mb-2">${escapeHtml(item.description)}</p>` : ''}
                </div>
            </div>
            
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="text-sm text-gray-700">${escapeHtml(truncatedText)}</p>
            </div>
            
            ${item.admin_note ? `
                <div class="bg-red-50 p-3 rounded-lg mb-4">
                    <p class="text-sm text-red-700"><strong>Lý do từ chối:</strong> ${escapeHtml(item.admin_note)}</p>
                </div>
            ` : ''}
            
            <div class="flex items-center justify-between">
                <button onclick="previewAudio('${escapeHtml(item.title)}', '${escapeHtml(item.text.replace(/'/g, "\\'"))}', '${item.audio_url}')" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                    🎧 Nghe thử
                </button>
                <div class="flex gap-2">
                    ${actions}
                </div>
            </div>
        </div>
    `;
}

function filterShares(status) {
    currentFilter = status;
    
    // Update button styles
    ['all', 'pending', 'approved', 'rejected'].forEach(s => {
        const btn = document.getElementById(`filter-${s}`);
        if (s === status) {
            btn.classList.remove('bg-gray-200', 'text-gray-700');
            btn.classList.add('bg-blue-500', 'text-white');
        } else {
            btn.classList.remove('bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-200', 'text-gray-700');
        }
    });
    
    loadShares(status);
}

async function approveShare(id) {
    if (!confirm('Bạn có chắc muốn duyệt yêu cầu này?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/share.php?action=approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Đã duyệt yêu cầu', 'success');
            loadShares(currentFilter);
        } else {
            showToast(data.error || 'Lỗi', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    }
}

function openRejectModal(id) {
    rejectingId = id;
    document.getElementById('reject-note').value = '';
    document.getElementById('reject-modal').classList.remove('hidden');
    document.getElementById('reject-modal').classList.add('flex');
}

function closeRejectModal() {
    rejectingId = null;
    document.getElementById('reject-modal').classList.add('hidden');
    document.getElementById('reject-modal').classList.remove('flex');
}

async function confirmReject() {
    if (!rejectingId) return;
    
    const note = document.getElementById('reject-note').value.trim();
    
    try {
        const response = await fetch(`${API_BASE}/share.php?action=reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: rejectingId, note })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Đã từ chối yêu cầu', 'success');
            closeRejectModal();
            loadShares(currentFilter);
        } else {
            showToast(data.error || 'Lỗi', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    }
}

function previewAudio(title, text, url) {
    document.getElementById('audio-modal-title').textContent = title;
    document.getElementById('audio-modal-text').textContent = text;
    document.getElementById('audio-modal-player').src = url;
    document.getElementById('audio-modal').classList.remove('hidden');
    document.getElementById('audio-modal').classList.add('flex');
}

function closeAudioModal() {
    document.getElementById('audio-modal-player').pause();
    document.getElementById('audio-modal').classList.add('hidden');
    document.getElementById('audio-modal').classList.remove('flex');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modals on backdrop click
document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
document.getElementById('audio-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAudioModal();
});

// Load on page ready
document.addEventListener('DOMContentLoaded', () => loadShares('all'));
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
