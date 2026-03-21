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

            echo json_encode([
                'success'  => true,
                'customer' => $result['customer'] ?? [],
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

        $api = new ClarityApiClient();
        $result = $api->register([
            'first_name'         => $firstName,
            'last_name'          => $lastName,
            'email'              => $email,
            'birth_month'        => $birthMonth,
            'birth_year'         => $birthYear,
            'research_confirmed' => true,
        ]);

        if (!empty($result['success'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Account created! Check your email at ' . htmlspecialchars($email) . ' for your temporary password, then sign in.',
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

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}
