// ========================================
// ARVEN PARFUME - Authentication System
// ========================================

// Konfigurasi
const AUTH_CONFIG = {
    loginPage: 'login.html',
    homePage: 'index.html',
    sessionTimeout: 30 * 60 * 1000, // 30 menit (dalam milliseconds)
};

// ========================================
// AUTHENTICATION FUNCTIONS
// ========================================

/**
 * Cek apakah user sudah login via API
 */
async function isAuthenticated() {
    try {
        const response = await fetch('api/check_auth.php');
        const data = await response.json();
        return data.authenticated === true;
    } catch (e) {
        console.error('Auth check error:', e);
        return false;
    }
}

/**
 * Ambil data user yang sedang login dari API
 */
async function getCurrentUser() {
    try {
        const response = await fetch('api/check_auth.php');
        const data = await response.json();
        
        if (data.authenticated && data.user) {
            return data.user;
        }
        return null;
    } catch (e) {
        console.error('Get user error:', e);
        return null;
    }
}

/**
 * Logout user
 */
async function logout() {
    try {
        const response = await fetch('api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirect ke halaman login
            window.location.href = AUTH_CONFIG.loginPage;
        }
    } catch (e) {
        console.error('Logout error:', e);
        // Tetap redirect meskipun ada error
        window.location.href = AUTH_CONFIG.loginPage;
    }
}

/**
 * Proteksi halaman - panggil di awal halaman yang memerlukan login
 */
async function requireAuth() {
    const authenticated = await isAuthenticated();
    if (!authenticated) {
        // Simpan URL tujuan untuk redirect setelah login
        sessionStorage.setItem('redirectAfterLogin', window.location.href);
        window.location.href = AUTH_CONFIG.loginPage;
        return false;
    }
    return true;
}

/**
 * Proteksi berdasarkan role
 */
async function requireRole(role) {
    const authenticated = await requireAuth();
    if (!authenticated) {
        return false;
    }
    
    const user = await getCurrentUser();
    if (!user) {
        window.location.href = AUTH_CONFIG.loginPage;
        return false;
    }
    
    // Admin bisa akses semua
    if (user.role === 'admin') {
        return true;
    }
    
    // Cek role spesifik
    if (user.role !== role) {
        alert('Anda tidak memiliki akses ke halaman ini!');
        window.location.href = AUTH_CONFIG.homePage;
        return false;
    }
    
    return true;
}

/**
 * Update UI berdasarkan status login
 */
async function updateAuthUI() {
    const user = await getCurrentUser();
    
    // Cari header navigation
    const headerNav = document.querySelector('.header-right-nav nav ul');
    
    if (!headerNav) {
        console.warn('Header navigation not found');
        return;
    }
    
    // Hapus elemen auth lama jika ada
    const oldAuthElement = document.getElementById('auth-element');
    if (oldAuthElement) {
        oldAuthElement.remove();
    }
    
    if (user) {
        // User sudah login - tampilkan greeting dan tombol logout
        const authElement = document.createElement('li');
        authElement.id = 'auth-element';
        authElement.style.cssText = 'display: flex; align-items: center; gap: 20px; margin-left: 20px;';
        
        authElement.innerHTML = `
            <span style="
                color: #c4a56a;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            ">
                Halo, ${user.name}
            </span>
            <button onclick="logout()" style="
                background: transparent;
                border: 1.5px solid #c4a56a;
                color: #c4a56a;
                padding: 8px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
                transition: all 0.3s ease;
                font-family: inherit;
            " 
            onmouseover="this.style.background='#c4a56a'; this.style.color='#161616';"
            onmouseout="this.style.background='transparent'; this.style.color='#c4a56a';">
                LOGOUT
            </button>
        `;
        
        headerNav.appendChild(authElement);
        
    } else {
        // User belum login - tampilkan tombol login
        const authElement = document.createElement('li');
        authElement.id = 'auth-element';
        authElement.style.cssText = 'margin-left: 20px;';
        
        authElement.innerHTML = `
            <a href="${AUTH_CONFIG.loginPage}" style="
                background: linear-gradient(135deg, #c4a56a, #a38b5d);
                color: #161616;
                padding: 10px 24px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 1.2px;
                text-transform: uppercase;
                transition: all 0.3s ease;
                display: inline-block;
                box-shadow: 0 4px 12px rgba(196, 165, 106, 0.3);
            "
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(196, 165, 106, 0.4)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(196, 165, 106, 0.3)';">
                LOGIN
            </a>
        `;
        
        headerNav.appendChild(authElement);
    }
}

/**
 * Refresh session (dipanggil otomatis setiap 5 menit)
 */
async function refreshSession() {
    try {
        // Cek auth akan otomatis refresh session di backend
        await fetch('api/check_auth.php');
    } catch (e) {
        console.error('Refresh session error:', e);
    }
}

// ========================================
// AUTO-INITIALIZATION
// ========================================

// Update UI saat halaman dimuat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuth);
} else {
    initAuth();
}

async function initAuth() {
    // Update UI dengan status login
    await updateAuthUI();
    
    // Refresh session setiap 5 menit jika user login
    const authenticated = await isAuthenticated();
    if (authenticated) {
        setInterval(refreshSession, 5 * 60 * 1000);
    }
}

// Export functions untuk digunakan di halaman lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        isAuthenticated,
        getCurrentUser,
        logout,
        requireAuth,
        requireRole,
        updateAuthUI,
        refreshSession
    };
}