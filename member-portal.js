// Mock Data
const MEMBERS = [
    { 
        id: 1, name: 'Nguyễn Văn An', email: 'an.nguyen@email.com', phone: '0901234567', 
        package: 'Premium 6 tháng', packageId: 3, trainer: 'PT Minh', trainerId: 1, 
        startDate: '2025-01-15', endDate: '2025-07-15', status: 'active', avatar: '👨', 
        qrCode: 'MEM001', totalSessions: 45, thisMonth: 12 
    },
    { 
        id: 2, name: 'Trần Thị Bình', email: 'binh.tran@email.com', phone: '0912345678', 
        package: 'Basic 3 tháng', packageId: 2, trainer: null, trainerId: null, 
        startDate: '2025-02-01', endDate: '2025-05-01', status: 'active', avatar: '👩', 
        qrCode: 'MEM002', totalSessions: 28, thisMonth: 8 
    },
];

const TRAINERS = [
    { id: 1, name: 'Nguyễn Văn Minh', specialization: 'Bodybuilding', experience: 5, rating: 4.8, avatar: '💪' },
    { id: 2, name: 'Trần Thị Lan', specialization: 'Yoga & Pilates', experience: 7, rating: 4.9, avatar: '🧘' },
];

const ATTENDANCE_HISTORY = [
    { memberId: 1, checkIn: '06:30', checkOut: '08:15', date: '2025-03-20', duration: '1h 45m' },
    { memberId: 1, checkIn: '07:00', checkOut: '09:00', date: '2025-03-19', duration: '2h 00m' },
    { memberId: 1, checkIn: '17:30', checkOut: '19:45', date: '2025-03-18', duration: '2h 15m' },
    { memberId: 1, checkIn: '06:15', checkOut: '08:00', date: '2025-03-17', duration: '1h 45m' },
    { memberId: 1, checkIn: '18:00', checkOut: '20:30', date: '2025-03-16', duration: '2h 30m' },
];

const SCHEDULES = [
    { id: 1, memberId: 1, trainerId: 1, date: '2025-03-22', time: '07:00', status: 'confirmed', type: 'PT Session' },
    { id: 2, memberId: 1, trainerId: 1, date: '2025-03-24', time: '07:00', status: 'pending', type: 'PT Session' },
];

// State
let currentTab = 'home';
let isCheckedIn = false;
let checkInTime = null;
let timerInterval = null;
let currentMember = null;

// Init
window.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (!currentUser || currentUser.role !== 'member') {
        window.location.href = 'login.html';
        return;
    }

    // Load member data
    currentMember = MEMBERS.find(m => m.id === currentUser.memberId) || MEMBERS[0];
    
    // Populate data
    populateMemberData();
    generateQRCode();
    loadAttendanceHistory();
    loadSchedules();
});

// Populate member data
function populateMemberData() {
    document.getElementById('headerGreeting').textContent = `Xin chào, ${currentMember.name}`;
    document.getElementById('memberName').textContent = currentMember.name;
    document.getElementById('memberQR').textContent = currentMember.qrCode;
    document.getElementById('packageName').textContent = currentMember.package;
    document.getElementById('packageDates').textContent = `${formatDate(currentMember.startDate)} - ${formatDate(currentMember.endDate)}`;
    document.getElementById('thisMonthSessions').textContent = currentMember.thisMonth;
    document.getElementById('totalSessions').textContent = currentMember.totalSessions;
    document.getElementById('qrMemberId').textContent = currentMember.qrCode;
    document.getElementById('qrMemberName').textContent = currentMember.name;
    document.getElementById('qrCodeId').textContent = currentMember.qrCode;
    
    // Days left
    const daysLeft = calculateDaysLeft(currentMember.endDate);
    const badgeText = daysLeft > 0 ? `Còn ${daysLeft} ngày` : 'Đã hết hạn';
    const badgeClass = daysLeft > 30 ? 'badge-active' : daysLeft > 0 ? 'badge-expiring' : 'badge-expired';
    
    document.getElementById('packageBadge').textContent = badgeText;
    document.getElementById('packageBadge').className = `badge ${badgeClass}`;
    
    // Progress
    const duration = 180; // 6 months in days
    const progress = Math.min(100, Math.max(0, 100 - (daysLeft / duration) * 100));
    document.getElementById('packageProgress').style.width = progress + '%';
    
    // Trainer info
    if (currentMember.trainerId) {
        const trainer = TRAINERS.find(t => t.id === currentMember.trainerId);
        if (trainer) {
            document.getElementById('trainerCard').style.display = 'block';
            document.getElementById('trainerAvatar').textContent = trainer.avatar;
            document.getElementById('trainerName').textContent = trainer.name;
            document.getElementById('trainerSpecialty').textContent = trainer.specialization;
            document.getElementById('trainerRating').textContent = trainer.rating;
            document.getElementById('trainerExperience').textContent = trainer.experience + ' năm KN';
        }
    }
}

// Generate QR Code
function generateQRCode() {
    const qrGrid = document.getElementById('qrGrid');
    qrGrid.innerHTML = '';
    
    for (let i = 0; i < 25; i++) {
        const pixel = document.createElement('div');
        pixel.className = 'qr-pixel';
        if (Math.random() > 0.5) {
            pixel.style.background = 'white';
        }
        qrGrid.appendChild(pixel);
    }
}

// Load attendance history
function loadAttendanceHistory() {
    const historyList = document.getElementById('historyList');
    const memberHistory = ATTENDANCE_HISTORY.filter(a => a.memberId === currentMember.id);
    
    historyList.innerHTML = memberHistory.map(record => `
        <div class="history-item">
            <div class="history-date">
                <span class="day">${record.date.split('-')[2]}</span>
                <span class="month">Th${record.date.split('-')[1]}</span>
            </div>
            <div class="history-time">
                <div class="time-range">
                    <span class="time-in">▶ ${record.checkIn}</span>
                    <span style="color: var(--text-tertiary);">→</span>
                    <span class="time-out">■ ${record.checkOut}</span>
                </div>
                <p>${formatDate(record.date)}</p>
            </div>
            <div class="history-duration">
                <p>${record.duration}</p>
                <p>Thời gian tập</p>
            </div>
        </div>
    `).join('');
}

// Load schedules
function loadSchedules() {
    const scheduleList = document.getElementById('scheduleList');
    const memberSchedules = SCHEDULES.filter(s => s.memberId === currentMember.id);
    
    if (memberSchedules.length === 0) {
        scheduleList.innerHTML = `
            <div class="text-center" style="padding: 32px; color: var(--text-tertiary);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 12px; opacity: 0.5;">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <p>Chưa có lịch tập nào</p>
            </div>
        `;
        return;
    }
    
    scheduleList.innerHTML = memberSchedules.map(schedule => {
        const trainer = TRAINERS.find(t => t.id === schedule.trainerId);
        const badgeClass = schedule.status === 'confirmed' ? 'badge-confirmed' : 'badge-pending';
        const badgeText = schedule.status === 'confirmed' ? 'Đã xác nhận' : 'Chờ xác nhận';
        
        return `
            <div class="schedule-item">
                <div class="schedule-header">
                    <span class="schedule-title">${schedule.type}</span>
                    <span class="${badgeClass}">${badgeText}</span>
                </div>
                <div class="schedule-details">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        ${formatDate(schedule.date)}
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        ${schedule.time}
                    </div>
                </div>
                ${trainer ? `
                    <div class="schedule-trainer">
                        <div class="trainer-avatar-small">${trainer.avatar}</div>
                        <span style="font-size: 14px;">${trainer.name}</span>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

// Switch tab
function switchTab(tab) {
    currentTab = tab;
    
    // Update content
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(tab + 'Tab').classList.add('active');
    
    // Update nav
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.closest('.nav-item').classList.add('active');
}

// Check-in handler
function handleCheckin() {
    if (!isCheckedIn) {
        // Check-in
        isCheckedIn = true;
        checkInTime = Date.now();
        
        const statusEl = document.getElementById('checkinStatus');
        statusEl.classList.add('active');
        
        const iconEl = document.getElementById('statusIcon');
        iconEl.classList.remove('inactive');
        iconEl.classList.add('active');
        iconEl.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        `;
        
        const textEl = document.getElementById('statusText');
        textEl.classList.remove('inactive');
        textEl.classList.add('active');
        textEl.textContent = 'Đang tập luyện';
        
        document.getElementById('timer').style.display = 'block';
        
        const btnEl = document.getElementById('checkinBtn');
        btnEl.className = 'btn-checkout';
        btnEl.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="6" y="4" width="4" height="16"></rect>
                <rect x="14" y="4" width="4" height="16"></rect>
            </svg>
            Check-out
        `;
        
        // Start timer
        timerInterval = setInterval(updateTimer, 1000);
    } else {
        // Check-out
        isCheckedIn = false;
        clearInterval(timerInterval);
        checkInTime = null;
        
        const statusEl = document.getElementById('checkinStatus');
        statusEl.classList.remove('active');
        
        const iconEl = document.getElementById('statusIcon');
        iconEl.classList.remove('active');
        iconEl.classList.add('inactive');
        iconEl.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h7v7H3z"></path>
                <path d="M14 3h7v7h-7z"></path>
                <path d="M14 14h7v7h-7z"></path>
                <path d="M3 14h7v7H3z"></path>
            </svg>
        `;
        
        const textEl = document.getElementById('statusText');
        textEl.classList.remove('active');
        textEl.classList.add('inactive');
        textEl.textContent = 'Bạn chưa check-in hôm nay';
        
        document.getElementById('timer').style.display = 'none';
        document.getElementById('timer').textContent = '00:00:00';
        
        const btnEl = document.getElementById('checkinBtn');
        btnEl.className = 'btn-checkin';
        btnEl.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
            Check-in ngay
        `;
    }
}

// Update timer
function updateTimer() {
    if (!checkInTime) return;
    
    const elapsed = Math.floor((Date.now() - checkInTime) / 1000);
    const hours = Math.floor(elapsed / 3600);
    const minutes = Math.floor((elapsed % 3600) / 60);
    const seconds = elapsed % 60;
    
    document.getElementById('timer').textContent = 
        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// Logout
function logout() {
    localStorage.removeItem('currentUser');
    window.location.href = 'login.html';
}

// Utility functions
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
