// Mock Data
const MEMBERS_DATA = [
    { 
        id: 1, name: 'Nguyễn Văn An', email: 'an.nguyen@email.com', phone: '0901234567', 
        package: 'Premium 6 tháng', trainer: 'PT Minh', 
        startDate: '2025-01-15', endDate: '2025-07-15', status: 'active', avatar: '👨', 
        qrCode: 'MEM001'
    },
    { 
        id: 2, name: 'Trần Thị Bình', email: 'binh.tran@email.com', phone: '0912345678', 
        package: 'Basic 3 tháng', trainer: '—', 
        startDate: '2025-02-01', endDate: '2025-05-01', status: 'active', avatar: '👩', 
        qrCode: 'MEM002'
    },
    { 
        id: 3, name: 'Lê Hoàng Cường', email: 'cuong.le@email.com', phone: '0923456789', 
        package: 'VIP 12 tháng', trainer: 'PT Lan', 
        startDate: '2024-06-01', endDate: '2025-06-01', status: 'expiring', avatar: '👨', 
        qrCode: 'MEM003'
    },
    { 
        id: 4, name: 'Phạm Thị Dung', email: 'dung.pham@email.com', phone: '0934567890', 
        package: 'Premium 6 tháng', trainer: 'PT Minh', 
        startDate: '2024-10-01', endDate: '2025-04-01', status: 'expired', avatar: '👩', 
        qrCode: 'MEM004'
    },
    { 
        id: 5, name: 'Hoàng Văn Em', email: 'em.hoang@email.com', phone: '0945678901', 
        package: 'Basic 1 tháng', trainer: '—', 
        startDate: '2025-03-01', endDate: '2025-04-01', status: 'active', avatar: '👨', 
        qrCode: 'MEM005'
    },
];

const PACKAGES_DATA = [
    { id: 1, name: 'Basic 1 tháng', price: 500000, description: 'Tập gym cơ bản', members: 45, color: '#6B7280', icon: '🎯' },
    { id: 2, name: 'Basic 3 tháng', price: 1200000, description: 'Tập gym 3 tháng', members: 78, color: '#3B82F6', icon: '💪' },
    { id: 3, name: 'Premium 6 tháng', price: 2500000, description: 'Gói cao cấp', members: 124, color: '#F59E0B', icon: '⭐' },
    { id: 4, name: 'VIP 12 tháng', price: 5000000, description: 'Gói VIP đặc biệt', members: 56, color: '#EF4444', icon: '👑' },
];

const TRAINERS_DATA = [
    { id: 1, name: 'Nguyễn Văn Minh', specialization: 'Bodybuilding', experience: 5, rating: 4.8, students: 15, avatar: '💪' },
    { id: 2, name: 'Trần Thị Lan', specialization: 'Yoga & Pilates', experience: 7, rating: 4.9, students: 22, avatar: '🧘' },
    { id: 3, name: 'Lê Văn Hùng', specialization: 'CrossFit', experience: 4, rating: 4.7, students: 18, avatar: '🏋️' },
    { id: 4, name: 'Phạm Thị Mai', specialization: 'Cardio & HIIT', experience: 3, rating: 4.6, students: 20, avatar: '🏃' },
];

let currentFilter = 'all';
let currentPage = 'dashboard';

// Init
window.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (!currentUser || currentUser.role !== 'admin') {
        window.location.href = 'login.html';
        return;
    }

    // Initialize
    loadMembers();
    loadPackages();
    loadTrainers();
    createRevenueChart();
});

// Toggle Sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}

// Show Page
function showPage(page) {
    currentPage = page;
    
    // Hide all pages
    document.querySelectorAll('.admin-page').forEach(p => p.classList.add('hidden'));
    
    // Show selected page
    document.getElementById(page + 'Page').classList.remove('hidden');
    
    // Update menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.closest('.menu-item').classList.add('active');
}

// Load Members
function loadMembers() {
    const tbody = document.getElementById('membersTableBody');
    document.getElementById('totalMembers').textContent = MEMBERS_DATA.length;
    
    const filteredMembers = currentFilter === 'all' 
        ? MEMBERS_DATA 
        : MEMBERS_DATA.filter(m => m.status === currentFilter);
    
    tbody.innerHTML = filteredMembers.map(member => {
        const daysLeft = calculateDaysLeft(member.endDate);
        const statusBadge = getStatusBadge(member.status, daysLeft);
        
        return `
            <tr>
                <td>
                    <div class="member-cell">
                        <div class="member-avatar-table">${member.avatar}</div>
                        <div class="member-details">
                            <p>${member.name}</p>
                            <p>ID: ${member.qrCode}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <p style="font-size: 14px;">${member.phone}</p>
                    <p style="font-size: 12px; color: var(--text-tertiary);">${member.email}</p>
                </td>
                <td>${member.package}</td>
                <td>${member.trainer}</td>
                <td>
                    <p style="font-size: 14px;">${formatDate(member.endDate)}</p>
                    <p style="font-size: 12px; color: var(--text-tertiary);">${daysLeft} ngày còn lại</p>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action view">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn-action edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-action delete">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Filter Members
function filterMembers(filter) {
    currentFilter = filter;
    
    // Update button states
    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    loadMembers();
}

// Load Packages
function loadPackages() {
    const grid = document.getElementById('packagesGrid');
    
    grid.innerHTML = PACKAGES_DATA.map((pkg, index) => `
        <div class="package-card ${index === 3 ? 'featured' : ''}">
            ${index === 3 ? '<span class="featured-badge">PHỔ BIẾN</span>' : ''}
            <div class="package-icon-large" style="background: ${pkg.color}20;">${pkg.icon}</div>
            <h3 class="package-name">${pkg.name}</h3>
            <p class="package-description">${pkg.description}</p>
            <p class="package-price" style="color: ${pkg.color};">${formatCurrency(pkg.price)}</p>
            <ul class="feature-list">
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Sử dụng máy tập
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Phòng tắm & tủ đồ
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    ${index >= 2 ? 'PT hướng dẫn' : 'Nước uống'}
                </li>
            </ul>
            <div class="package-footer">
                <span class="package-members">${pkg.members} hội viên</span>
                <div class="action-buttons">
                    <button class="btn-action edit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="btn-action delete">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Load Trainers
function loadTrainers() {
    const grid = document.getElementById('trainersGrid');
    
    grid.innerHTML = TRAINERS_DATA.map(trainer => `
        <div class="trainer-card-admin">
            <div class="trainer-header">
                <div class="trainer-avatar-large">${trainer.avatar}</div>
                <div class="trainer-details">
                    <div class="trainer-name-row">
                        <h3 class="trainer-name">${trainer.name}</h3>
                        <div class="trainer-rating-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>${trainer.rating}</span>
                        </div>
                    </div>
                    <p class="trainer-specialty-admin">${trainer.specialization}</p>
                    <p class="trainer-experience">${trainer.experience} năm kinh nghiệm</p>
                </div>
            </div>
            <div class="trainer-stats">
                <div class="trainer-stat-item">
                    <p class="trainer-stat-value" style="color: #60a5fa;">${trainer.students}</p>
                    <p class="trainer-stat-label">Học viên</p>
                </div>
                <div class="trainer-stat-item">
                    <p class="trainer-stat-value" style="color: #86efac;">2</p>
                    <p class="trainer-stat-label">Chứng chỉ</p>
                </div>
                <div class="trainer-stat-item">
                    <p class="trainer-stat-value" style="color: #fb923c;">6:00</p>
                    <p class="trainer-stat-label">Ca làm</p>
                </div>
            </div>
            <div class="certification-tags">
                <span class="cert-tag">ACE</span>
                <span class="cert-tag">NASM</span>
            </div>
            <div class="trainer-actions">
                <button class="btn-secondary">Xem chi tiết</button>
                <button class="btn-assign">Phân công</button>
            </div>
        </div>
    `).join('');
}

// Create Revenue Chart
function createRevenueChart() {
    const chart = document.getElementById('revenueChart');
    const months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
    
    chart.innerHTML = months.map((month, i) => {
        const height = 30 + Math.random() * 70;
        return `
            <div class="chart-bar">
                <div class="bar" style="height: ${height}%;"></div>
                <span class="bar-label">${month}</span>
            </div>
        `;
    }).join('');
}

// Logout
function logout() {
    localStorage.removeItem('currentUser');
    window.location.href = 'login.html';
}

// Utility Functions
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function calculateDaysLeft(endDate) {
    const end = new Date(endDate);
    const today = new Date();
    const diffTime = end - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

function getStatusBadge(status, daysLeft) {
    if (daysLeft <= 0) {
        return '<span class="badge badge-expired">Hết hạn</span>';
    } else if (daysLeft <= 30) {
        return '<span class="badge badge-expiring">Sắp hết hạn</span>';
    } else {
        return '<span class="badge badge-active">Hoạt động</span>';
    }
}
