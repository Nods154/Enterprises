<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['customer_id']);
$customer_name = $is_logged_in ? $_SESSION['customer_name'] : '';
$customer_first_name = $is_logged_in ? $_SESSION['customer_first_name'] : '';
?>

<!-- Account Panel Content - Add this to NaviLinks.php -->
<div id="anavi-links" class="nav-links account-panel">
    <div class="nav-links-inner">
        
        <?php if (!$is_logged_in): ?>
        <!-- For Guests (Not Logged In) -->
        <div id="guest-panel" class="account-content">
            <div class="account-header">
                <h2>Welcome</h2>
                <p>Sign in to your account or create a new one</p>
            </div>
            
            <!-- Login Form -->
            <div id="login-form-container" class="form-container active">
                <form id="login-form" method="POST" action="/login-process.php">
                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <div class="form-message" id="login-message"></div>
                    
                    <button type="submit" class="btn-primary">Sign In</button>
                    
                    <div class="form-switch">
                        Don't have an account? 
                        <a href="#" id="show-register">Create one</a>
                    </div>
                </form>
            </div>
            
            <!-- Registration Form -->
            <div id="register-form-container" class="form-container">
                <form id="register-form" method="POST" action="/register-process.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-firstname">First Name *</label>
                            <input type="text" id="reg-firstname" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-lastname">Last Name *</label>
                            <input type="text" id="reg-lastname" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-email">Email Address *</label>
                        <input type="email" id="reg-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-phone">Phone Number</label>
                        <input type="tel" id="reg-phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password">Password *</label>
                        <input type="password" id="reg-password" name="password" required minlength="8">
                        <small>Must be at least 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-confirm">Confirm Password *</label>
                        <input type="password" id="reg-confirm" name="confirm_password" required minlength="8">
                    </div>
                    
                    <div class="form-message" id="register-message"></div>
                    
                    <button type="submit" class="btn-primary">Create Account</button>
                    
                    <div class="form-switch">
                        Already have an account? 
                        <a href="#" id="show-login">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <!-- For Logged In Users -->
        <div id="user-panel" class="account-content">
            <div class="account-header">
                <h2>My Account</h2>
                <p id="user-welcome">Welcome back, <?php echo htmlspecialchars($customer_first_name); ?>!</p>
            </div>
            
            <nav class="account-menu">
                <a href="/account.php" class="menu-item">
                    <i class="icon-user"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/orders.php" class="menu-item">
                    <i class="icon-list"></i>
                    <span>My Orders</span>
                </a>
                <a href="/addresses.php" class="menu-item">
                    <i class="icon-location"></i>
                    <span>Addresses</span>
                </a>
                <a href="/payment-methods.php" class="menu-item">
                    <i class="icon-credit-card"></i>
                    <span>Payment Methods</span>
                </a>
                <a href="/profile.php" class="menu-item">
                    <i class="icon-settings"></i>
                    <span>Settings</span>
                </a>
                <a href="/logout.php" class="menu-item logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* Account Panel Sliding from Top */
.account-panel {
    position: fixed;
    top: -100%;
    left: 0;
    right: 0;
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    z-index: 999;
    transition: top 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
    border-radius: 0 0 12px 12px;
    max-height: 90vh;
    overflow-y: auto;
}

.account-panel:target {
    top: 0;
}

.nav-links-inner {
    padding: 30px;
}

.account-header {
    text-align: center;
    margin-bottom: 30px;
}

.account-header h2 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 26px;
}

.account-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

/* Form Styling */
.form-container {
    display: none;
}

.form-container.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #ECDA98;
}

.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #7f8c8d;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 13px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.remember-me input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

.forgot-link {
    color: #3498db;
    text-decoration: none;
}

.forgot-link:hover {
    text-decoration: underline;
}

.form-message {
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
    display: none;
}

.form-message.error {
    display: block;
    background: #fadbd8;
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.form-message.success {
    display: block;
    background: #d5f4e6;
    color: #27ae60;
    border: 1px solid #27ae60;
}

.btn-primary {
    width: 100%;
    padding: 14px;
    background: #ECDA98;
    color: #000;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #E5D28A;
    transform: translateY(-2px);
}

.form-switch {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #7f8c8d;
}

.form-switch a {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.form-switch a:hover {
    text-decoration: underline;
}

/* Account Menu (Logged In) */
.account-menu {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
}

.menu-item:hover {
    background: #f8f9fa;
}

.menu-item.logout {
    color: #e74c3c;
    margin-top: 10px;
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
}

.menu-item i {
    font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .account-panel {
        max-width: 100%;
        border-radius: 0 0 12px 12px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Toggle between login and register forms
document.addEventListener('DOMContentLoaded', function() {
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const loginContainer = document.getElementById('login-form-container');
    const registerContainer = document.getElementById('register-form-container');
    
    if (showRegister) {
        showRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginContainer.classList.remove('active');
            registerContainer.classList.add('active');
        });
    }
    
    if (showLogin) {
        showLogin.addEventListener('click', function(e) {
            e.preventDefault();
            registerContainer.classList.remove('active');
            loginContainer.classList.add('active');
        });
    }
    
    // Handle login form submission
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }
    
    // Handle register form submission
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRegister();
        });
    }
    
    // Update account label if logged in
    <?php if ($is_logged_in): ?>
    const accountLabel = document.getElementById('account-label');
    if (accountLabel) {
        accountLabel.textContent = '<?php echo htmlspecialchars($customer_first_name); ?>';
    }
    <?php endif; ?>
});

function handleLogin() {
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('login-message');
    
    fetch('/login-process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.className = 'form-message success';
            messageDiv.textContent = 'Login successful! Redirecting...';
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            messageDiv.className = 'form-message error';
            messageDiv.textContent = data.message || 'Login failed. Please try again.';
        }
    })
    .catch(error => {
        messageDiv.className = 'form-message error';
        messageDiv.textContent = 'An error occurred. Please try again.';
    });
}

function handleRegister() {
    const form = document.getElementById('register-form');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('register-message');
    
    // Check if passwords match
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm').value;
    
    if (password !== confirmPassword) {
        messageDiv.className = 'form-message error';
        messageDiv.textContent = 'Passwords do not match.';
        return;
    }
    
    fetch('/register-process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.className = 'form-message success';
            messageDiv.textContent = 'Account created successfully! Redirecting...';
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            messageDiv.className = 'form-message error';
            messageDiv.textContent = data.message || 'Registration failed. Please try again.';
        }
    })
    .catch(error => {
        messageDiv.className = 'form-message error';
        messageDiv.textContent = 'An error occurred. Please try again.';
    });
}
</script>
