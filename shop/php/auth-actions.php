<?php
/* ============================================================
   ClarityLabsUSA — Auth Actions (AJAX Handler)
   Handles login, register, logout via clarity-ops API
   ============================================================ */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/api-client.php';

clarity_session_start();

$action = $_GET['action'] ?? '';

switch ($action) {

    /* ──────────────────────────────────────────
       LOGIN
       ────────────────────────────────────────── */
    case 'login':
        csrf_verify();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->login($email, $password);

        if (!empty($result['success']) && !empty($result['token'])) {
            // Store customer data + token in session
            set_customer($result['customer'] ?? [], $result['token']);

            // Check if password change is required
            $mustChangePassword = !empty($result['must_change_password']);
            if ($mustChangePassword) {
                $_SESSION['must_change_password'] = true;
            }

            echo json_encode([
                'success'              => true,
                'customer'             => $result['customer'] ?? [],
                'must_change_password' => $mustChangePassword,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error'   => $result['message'] ?? $result['error'] ?? 'Invalid email or password.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       REGISTER
       ────────────────────────────────────────── */
    case 'register':
        csrf_verify();

        $firstName   = trim($_POST['first_name'] ?? '');
        $lastName    = trim($_POST['last_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $smsOptIn    = !empty($_POST['sms_opt_in']);
        $birthMonth  = (int) ($_POST['birth_month'] ?? 0);
        $birthYear   = (int) ($_POST['birth_year'] ?? 0);
        $researchOk  = !empty($_POST['research_confirmed']);

        // Validation
        $errors = [];
        if (empty($firstName))  $errors[] = 'First name is required.';
        if (empty($lastName))   $errors[] = 'Last name is required.';
        if (empty($email))      $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if ($birthMonth < 1 || $birthMonth > 12) $errors[] = 'Birth month is required.';
        if ($birthYear < 1900 || $birthYear > (int) date('Y')) $errors[] = 'Birth year is required.';
        if (!$researchOk) $errors[] = 'You must confirm research use.';

        // Age check (must be 21+)
        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');
        $age = $currentYear - $birthYear;
        if ($currentMonth < $birthMonth) $age--;
        if ($age < 21) {
            $errors[] = 'You must be 21 years of age or older to register.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
            exit;
        }

        // Generate a temporary password (customer will be forced to change on first login)
        $tempPassword = bin2hex(random_bytes(6)); // 12-char random string

        $api = new ClarityApiClient();
        $result = $api->register([
            'first_name'         => $firstName,
            'last_name'          => $lastName,
            'email'              => $email,
            'phone'              => $phone ?: null,
            'sms_opt_in'         => $smsOptIn,
            'password'           => $tempPassword,
            'birth_month'        => $birthMonth,
            'birth_year'         => $birthYear,
            'research_confirmed' => true,
        ]);

        if (!empty($result['success'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Account created! Check your email at ' . htmlspecialchars($email) . ' for your temporary password. Sign in with that password and you will be asked to set a new one.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error'   => $result['message'] ?? $result['error'] ?? 'Registration failed. Please try again.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       LOGOUT
       ────────────────────────────────────────── */
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'POST required.']);
            exit;
        }
        csrf_verify();

        $token = get_customer_token();
        if ($token) {
            $api = new ClarityApiClient();
            $api->logout($token);
        }
        clear_customer();

        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
        break;

    /* ──────────────────────────────────────────
       FORGOT PASSWORD
       ────────────────────────────────────────── */
    case 'forgot-password':
        csrf_verify();

        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->forgotPassword($email);

        // Always show success to prevent email enumeration
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with that email, you\'ll receive a password reset link shortly.',
        ]);
        break;

    /* ──────────────────────────────────────────
       FORGOT PASSWORD VIA SMS
       ────────────────────────────────────────── */
    case 'forgot-password-sms':
        csrf_verify();

        $phone = trim($_POST['phone'] ?? '');
        if (empty($phone) || strlen($phone) < 10) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid phone number.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->forgotPasswordSms($phone);

        // Always show success to prevent phone enumeration
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with that phone number, you\'ll receive a reset code shortly.',
        ]);
        break;

    /* ──────────────────────────────────────────
       VERIFY SMS RESET CODE
       ────────────────────────────────────────── */
    case 'verify-reset-code':
        csrf_verify();

        $phone = trim($_POST['phone'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if (empty($phone) || empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Phone number and code are required.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->verifyResetCode($phone, $code);

        if (!empty($result['success']) && !empty($result['token'])) {
            echo json_encode([
                'success' => true,
                'token' => $result['token'],
                'message' => $result['message'] ?? 'Code verified.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? 'Invalid or expired code. Please try again.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       RESET PASSWORD (from email link or SMS code)
       ────────────────────────────────────────── */
    case 'reset-password':
        csrf_verify();

        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        if (empty($token)) {
            echo json_encode(['success' => false, 'error' => 'Invalid reset link.']);
            exit;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters.']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->resetPassword([
            'token' => $token,
            'new_password' => $newPassword,
            'new_password_confirmation' => $confirmPassword,
        ]);

        if (!empty($result['success'])) {
            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? 'Password reset successfully. You can now log in.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? 'Failed to reset password. The link may have expired.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       CHANGE PASSWORD (first login)
       ────────────────────────────────────────── */
    case 'change-password':
        csrf_verify();

        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
            exit;
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters.']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->changePassword([
            'new_password' => $newPassword,
            'new_password_confirmation' => $confirmPassword,
        ], get_customer_token());

        if (!empty($result['success'])) {
            unset($_SESSION['must_change_password']);
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? $result['error'] ?? 'Failed to update password.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       UPDATE PROFILE (account settings)
       ────────────────────────────────────────── */
    case 'update-profile':
        csrf_verify();

        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
            exit;
        }

        $payload = [];
        foreach (['first_name', 'last_name', 'email', 'phone'] as $field) {
            if (isset($_POST[$field])) {
                $payload[$field] = trim((string) $_POST[$field]);
            }
        }
        if (isset($_POST['sms_opt_in'])) {
            $payload['sms_opt_in'] = !empty($_POST['sms_opt_in']) && $_POST['sms_opt_in'] !== '0';
        }

        if (empty($payload)) {
            echo json_encode(['success' => false, 'error' => 'Nothing to update.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->updateProfile($payload, get_customer_token());

        if (!empty($result['success']) || ($result['status'] ?? null) === 'ok') {
            // Refresh session customer cache
            $me = $api->getMe(get_customer_token());
            if (!empty($me['data'])) {
                set_customer($me['data'], get_customer_token());
            }
            // Email changes are pending until confirmed from the new inbox —
            // surface the API's message so the customer knows to check it
            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? 'Profile updated successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? $result['error'] ?? 'Failed to update profile.',
                'errors' => $result['errors'] ?? null,
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       MARK ORDER PAID — customer self-report
       ────────────────────────────────────────── */
    case 'mark-order-paid':
        csrf_verify();

        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $provider = $_POST['provider'] ?? '';
        $reference = trim((string) ($_POST['reference'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($orderId < 1 || !in_array($provider, ['venmo', 'zelle'], true)) {
            echo json_encode(['success' => false, 'error' => 'Invalid order or payment method.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->markOrderPaidSelfReport($orderId, [
            'provider' => $provider,
            'reference' => $reference ?: null,
            'notes' => $notes ?: null,
        ], get_customer_token());

        if (!empty($result['success']) || ($result['status'] ?? null) === 'ok') {
            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? 'Thanks — we\'ll confirm your payment shortly.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? $result['error'] ?? 'Failed to record payment.',
            ]);
        }
        break;

    /* ──────────────────────────────────────────
       SMS CONFIRM (web double opt-in)
       ────────────────────────────────────────── */
    case 'sms-confirm':
        csrf_verify();

        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
            exit;
        }

        $confirmationText = trim((string) ($_POST['confirmation_text'] ?? ''));
        if ($confirmationText === '') {
            echo json_encode(['success' => false, 'error' => 'Type CONFIRM to finalize.']);
            exit;
        }

        $api = new ClarityApiClient();
        $result = $api->confirmSms($confirmationText, get_customer_token());

        if (!empty($result['success']) || ($result['status'] ?? null) === 'ok') {
            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? 'SMS notifications confirmed.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['message'] ?? $result['error'] ?? 'Confirmation failed.',
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}
