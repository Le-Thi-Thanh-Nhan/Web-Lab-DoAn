<?php
function renderLoginButton($customClass = '') {
    $class = 'auth-button login-button ' . $customClass;
    return sprintf(
        '<a href="../auth/auth.php" class="%s">
            <i class="fas fa-sign-in-alt"></i>Đăng nhập
        </a>',
        htmlspecialchars($class)
    );
}

function renderRegisterButton($customClass = '') {
    $class = 'auth-button register-button ' . $customClass;
    return sprintf(
        '<a href="../auth/auth.php" class="%s">
            <i class="fas fa-user-plus"></i>Đăng ký
        </a>',
        htmlspecialchars($class)
    );
}

function renderAuthButtons($containerClass = 'auth-buttons') {
    return sprintf(
        '<div class="%s">
            %s
            %s
        </div>',
        htmlspecialchars($containerClass),
        renderLoginButton(),
        renderRegisterButton()
    );
}

// CSS styles for auth buttons
function renderAuthButtonStyles() {
    return '
    <style>
        /* Auth buttons container */
        .auth-buttons {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Common button styles */
        .auth-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            margin: 0;
            box-sizing: border-box;
            font-size: 16px;
            cursor: pointer;
        }

        .auth-button i {
            margin-right: 12px;
            font-size: 18px;
        }

        /* Login button specific styles */
        .login-button {
            background-color: #4CAF50;
            color: white !important;
            border: none;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
        }

        .login-button:hover {
            background-color: #45a049;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
            transform: translateY(-1px);
        }

        /* Register button specific styles */
        .register-button {
            background-color: #ffffff;
            color: #4CAF50 !important;
            border: 2px solid #4CAF50;
            margin-top: 8px;
        }

        .register-button:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
    </style>
    ';
}
?> 