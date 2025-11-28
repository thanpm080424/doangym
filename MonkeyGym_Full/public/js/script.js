// ===================================
// MONKEY GYM - JAVASCRIPT
// ===================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initDashboard();
    initCharts();
    initEventListeners();
    loadMockData();
});

// ===================================
// NAVIGATION & SECTION SWITCHING
// ===================================

function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update active nav link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ===================================
// DASHBOARD INITIALIZATION
// ===================================

function initDashboard() {
    // Update statistics with animation
    animateValue('totalMembers', 0, 245, 2000);
    animateValue('activeMembers', 0, 198, 2000);
    animateValue('expiringMembers', 0, 7, 2000);
    
    // Format revenue
    setTimeout(() => {
        document.getElementById('monthlyRevenue').textContent = '125,500,000đ';
    }, 1000);
}

// Animate numbers
function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// ===================================
// CHARTS INITIALIZATION
// ===================================

let revenueChart;
let packageChart;

function initCharts() {
    initRevenueChart();
    initPackageChart();
}

function initRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    
    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: [85, 92, 105, 98, 115, 120, 118, 125, 130, 122, 135, 125],
                borderColor: 'rgb(255, 193, 7)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(255, 193, 7)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' triệu';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' tr';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function initPackageChart() {
    const ctx = document.getElementById('packageChart');
    if (!ctx) return;
    
    packageChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Gói 1 tháng', 'Gói 3 tháng', 'Gói 6 tháng', 'Gói 12 tháng'],
            datasets: [{
                data: [45, 78, 92, 30],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' HV (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// ===================================
// EVENT LISTENERS
// ===================================

function initEventListeners() {
    // Form submission
    const addMemberForm = document.getElementById('addMemberForm');
    if (addMemberForm) {
        addMemberForm.addEventListener('submit', handleAddMember);
    }
    
    // Search functionality
    const searchInputs = document.querySelectorAll('input[type="text"][placeholder*="Tìm kiếm"]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', debounce(handleSearch, 300));
    });
}

// ===================================
// FORM HANDLERS
// ===================================

function handleAddMember(e) {
    e.preventDefault();
    
    // Show loading
    showLoading();
    
    // Simulate API call
    setTimeout(() => {
        hideLoading();
        showNotification('Thêm hội viên thành công!', 'success');
        
        // Reset form
        e.target.reset();
        
        // Go back to members list
        showSection('members');
        
        // Reload mock data
        loadMockData();
    }, 1500);
}

// ===================================
// SEARCH FUNCTIONALITY
// ===================================

function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tableRows = document.querySelectorAll('#membersTableBody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Debounce function for search
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

// ===================================
// NOTIFICATIONS
// ===================================

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <strong>${type === 'success' ? 'Thành công!' : 'Thông báo'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// ===================================
// LOADING SPINNER
// ===================================

function showLoading() {
    const spinner = document.createElement('div');
    spinner.id = 'loadingSpinner';
    spinner.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
    spinner.style.cssText = 'background: rgba(0,0,0,0.5); z-index: 99999;';
    spinner.innerHTML = `
        <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    document.body.appendChild(spinner);
}

function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.remove();
    }
}

// ===================================
// MOCK DATA LOADING
// ===================================

function loadMockData() {
    // Mock data for members
    const mockMembers = [
        {
            id: 'HV001',
            name: 'Nguyễn Văn A',
            email: 'nguyenvana@email.com',
            phone: '0901234567',
            package: 'Gói 6 tháng',
            startDate: '01/06/2024',
            endDate: '01/12/2024',
            status: 'active'
        },
        {
            id: 'HV002',
            name: 'Trần Thị B',
            email: 'tranthib@email.com',
            phone: '0907654321',
            package: 'Gói 3 tháng',
            startDate: '15/08/2024',
            endDate: '15/11/2024',
            status: 'expiring'
        },
        {
            id: 'HV003',
            name: 'Lê Văn C',
            email: 'levanc@email.com',
            phone: '0903456789',
            package: 'Gói 12 tháng',
            startDate: '10/01/2024',
            endDate: '10/01/2025',
            status: 'active'
        },
        {
            id: 'HV004',
            name: 'Phạm Thị D',
            email: 'phamthid@email.com',
            phone: '0909876543',
            package: 'Gói 1 tháng',
            startDate: '20/10/2024',
            endDate: '20/11/2024',
            status: 'expiring'
        },
        {
            id: 'HV005',
            name: 'Hoàng Văn E',
            email: 'hoangvane@email.com',
            phone: '0905555555',
            package: 'Gói 6 tháng',
            startDate: '05/07/2024',
            endDate: '05/01/2025',
            status: 'active'
        }
    ];
    
    // Update table
    updateMembersTable(mockMembers);
}

function updateMembersTable(members) {
    const tbody = document.getElementById('membersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = members.map(member => {
        const statusBadge = member.status === 'active' 
            ? '<span class="badge bg-success">Hoạt động</span>'
            : '<span class="badge bg-warning">Sắp hết hạn</span>';
            
        const packageBadge = member.package.includes('12')
            ? 'bg-primary'
            : member.package.includes('6')
            ? 'bg-info'
            : member.package.includes('3')
            ? 'bg-warning'
            : 'bg-secondary';
            
        return `
            <tr>
                <td>${member.id}</td>
                <td>${member.name}</td>
                <td>${member.email}</td>
                <td>${member.phone}</td>
                <td><span class="badge ${packageBadge}">${member.package}</span></td>
                <td>${member.startDate}</td>
                <td>${member.endDate}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewMember('${member.id}')" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editMember('${member.id}')" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteMember('${member.id}')" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// ===================================
// MEMBER ACTIONS
// ===================================

function viewMember(id) {
    showNotification(`Xem chi tiết hội viên ${id}`, 'info');
    // TODO: Implement view member details
}

function editMember(id) {
    showNotification(`Sửa thông tin hội viên ${id}`, 'info');
    // TODO: Implement edit member
}

function deleteMember(id) {
    if (confirm(`Bạn có chắc chắn muốn xóa hội viên ${id}?`)) {
        showLoading();
        setTimeout(() => {
            hideLoading();
            showNotification(`Đã xóa hội viên ${id}`, 'success');
            loadMockData();
        }, 1000);
    }
}

// ===================================
// UTILITY FUNCTIONS
// ===================================

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('vi-VN');
}

// Calculate days remaining
function daysRemaining(endDate) {
    const end = new Date(endDate);
    const today = new Date();
    const diff = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
    return diff;
}

// ===================================
// AUTO-UPDATE DASHBOARD
// ===================================

// Update dashboard every 30 seconds
setInterval(() => {
    // Update recent activities
    updateRecentActivities();
    
    // Update notifications
    updateNotifications();
}, 30000);

function updateRecentActivities() {
    // TODO: Fetch from API
    console.log('Updating recent activities...');
}

function updateNotifications() {
    // TODO: Fetch from API
    console.log('Updating notifications...');
}

// ===================================
// PRINT FUNCTIONALITY
// ===================================

function printReport() {
    window.print();
}

// ===================================
// EXPORT FUNCTIONALITY
// ===================================

function exportToExcel() {
    showNotification('Đang xuất dữ liệu ra Excel...', 'info');
    // TODO: Implement Excel export
}

function exportToPDF() {
    showNotification('Đang xuất dữ liệu ra PDF...', 'info');
    // TODO: Implement PDF export
}

// ===================================
// KEYBOARD SHORTCUTS
// ===================================

document.addEventListener('keydown', function(e) {
    // Ctrl + S: Quick save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        showNotification('Đang lưu...', 'info');
    }
    
    // Ctrl + F: Focus search
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="text"][placeholder*="Tìm kiếm"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
});

// ===================================
// MAKE FUNCTIONS GLOBAL
// ===================================

window.showSection = showSection;
window.viewMember = viewMember;
window.editMember = editMember;
window.deleteMember = deleteMember;
window.printReport = printReport;
window.exportToExcel = exportToExcel;
window.exportToPDF = exportToPDF;

console.log('%c🐒 Monkey Gym System Loaded Successfully! ', 'background: #222; color: #ffc107; font-size: 16px; font-weight: bold; padding: 10px;');
