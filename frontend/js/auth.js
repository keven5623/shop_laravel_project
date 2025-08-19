window.token = localStorage.getItem('token') || null;
window.authUserId = parseInt(localStorage.getItem('authUserId')) || null;
window.user = null;
window.initialized = false;

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('loginBtn').addEventListener('click', login);
    document.getElementById('logoutBtn').addEventListener('click', logout);

    checkProfileOnLoad();
});

async function login() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    if (!email || !password) return showMessage('loginMessage', '請輸入 Email 與密碼');

    try {
        const res = await fetch(`${API_BASE}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (!res.ok) return showMessage('loginMessage', data.message || '登入失敗');

        window.token = data.token;
        localStorage.setItem('token', window.token);

        // 儲存 userId
        window.authUserId = data.user.id;
        localStorage.setItem('authUserId', window.authUserId);

        await loadProfile();
    } catch {
        showMessage('loginMessage', '登入錯誤，請稍後再試');
    }
}

async function loadProfile() {
    if (!window.token) return showLogin();
    try {
        const res = await fetch(`${API_BASE}/me`, {
            headers: { Authorization: `Bearer ${window.token}` }
        });
        if (!res.ok) throw new Error();
        window.user = await res.json();

        // 更新 userId，避免頁面重整後 undefined
        window.authUserId = window.user.id;
        localStorage.setItem('authUserId', window.authUserId);

        showUser();

        if (!window.initialized) {
            window.initialized = true;
            window.dispatchEvent(new Event('user-logged-in'));
        }
    } catch {
        logout();
    }
}

function logout() {
    window.token = null;
    window.user = null;
    window.authUserId = null;
    localStorage.removeItem('token');
    localStorage.removeItem('authUserId');
    showLogin();
    clearData();
    window.initialized = false;
    window.dispatchEvent(new Event('user-logged-out'));
}

function showLogin() {
    document.getElementById('loginForm').classList.remove('d-none');
    document.getElementById('userPanel').classList.add('d-none');
}

function showUser() {
    document.getElementById('loginForm').classList.add('d-none');
    document.getElementById('userPanel').classList.remove('d-none');
    document.getElementById('username').innerText = window.user.name || window.user.email || '使用者';
}

function showMessage(id, msg) {
    document.getElementById(id).innerText = msg;
}

function clearData() {
    document.getElementById('productList').innerHTML = '';
    document.getElementById('categoryList').innerHTML = '';
    document.getElementById('cartList').innerHTML = '';
}

async function checkProfileOnLoad() {
    const app = document.getElementById('app');

    if (!window.token) {
        showLogin();
    } else {
        await loadProfile();
    }

    app.classList.remove('d-none'); // 只有檢查完成後才顯示頁面
}

// 切換表單
document.getElementById('showRegisterBtn').addEventListener('click', () => {
    document.getElementById('loginForm').classList.add('d-none');
    document.getElementById('registerForm').classList.remove('d-none');
    document.getElementById('registerMessage').textContent = '';
});

document.getElementById('showLoginBtn').addEventListener('click', () => {
    document.getElementById('registerForm').classList.add('d-none');
    document.getElementById('loginForm').classList.remove('d-none');
    document.getElementById('loginMessage').textContent = '';
});

// 註冊
document.getElementById('registerBtn').addEventListener('click', async () => {
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value;
    const password_confirmation = document.getElementById('regPasswordConfirm').value;
    const msg = document.getElementById('registerMessage');

    msg.textContent = '';

    if (!name || !email || !password || !password_confirmation) {
        msg.textContent = '請完整填寫所有欄位';
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email, password, password_confirmation })
        });

        const data = await res.json();

        if (!res.ok) {
            msg.textContent = data.message || '註冊失敗';
            return;
        }

        alert('註冊成功！請重新登入');
        document.getElementById('registerForm').classList.add('d-none');
        document.getElementById('loginForm').classList.remove('d-none');
        document.getElementById('email').value = email;
        document.getElementById('password').value = '';
    } catch (error) {
        msg.textContent = '系統錯誤，請稍後再試';
    }
});
