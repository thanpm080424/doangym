// Mock user data
const USERS = [
    { id: 1, username: 'admin', password: 'admin123', role: 'admin', name: 'Quản trị viên', avatar: '👨‍💼' },
    { id: 2, username: 'member1', password: '123456', role: 'member', name: 'Nguyễn Văn An', memberId: 1, avatar: '👨' },
    { id: 3, username: 'member2', password: '123456', role: 'member', name: 'Trần Thị Bình', memberId: 2, avatar: '👩' },
];

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
}

// Handle login
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('errorMessage');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');
    
    // Find user
    const user = USERS.find(u => u.username === username && u.password === password);
    
    if (user) {
        // Hide error
        errorMessage.style.display = 'none';
        
        // Show loading
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitLoader.style.display = 'block';
        
        // Save to localStorage
        localStorage.setItem('currentUser', JSON.stringify(user));
        
        // Redirect after 800ms
        setTimeout(() => {
            if (user.role === 'admin') {
                window.location.href = 'admin-dashboard.html';
            } else {
                window.location.href = 'member-portal.html';
            }
        }, 800);
    } else {
        // Show error
        errorMessage.style.display = 'flex';
        
        // Shake animation
        document.querySelector('.login-box').style.animation = 'shake 0.5s';
        setTimeout(() => {
            document.querySelector('.login-box').style.animation = '';
        }, 500);
    }
});

// Shake animation
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);

// Check if already logged in
window.addEventListener('DOMContentLoaded', function() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (currentUser) {
        // Redirect to appropriate page
        if (currentUser.role === 'admin') {
            window.location.href = 'admin-dashboard.html';
        } else {
            window.location.href = 'member-portal.html';
        }
    }
});
