<?php
/* ============================================================
   ClarityLabsUSA — Order Form Mailer
   Sends two emails:
   1. Customer confirmation + COA PDF attachment (from order@claritylabsbio.com)
   2. Admin notification (to johnathan.mericamarketing@gmail.com)
   ============================================================ */

header('Content-Type: application/json');

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// ── Honeypot check ──
if (!empty($_POST['website'])) {
    echo json_encode(['success' => false, 'message' => 'Spam detected.']);
    exit;
}

// ── Collect & sanitize fields ──
$product     = htmlspecialchars(trim($_POST['product'] ?? ''));
$productSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['product_slug'] ?? '')));
$size        = htmlspecialchars(trim($_POST['size'] ?? ''));
$price       = htmlspecialchars(trim($_POST['price'] ?? ''));
$quantity    = intval($_POST['quantity'] ?? 1);
$firstName   = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$lastName    = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone       = htmlspecialchars(trim($_POST['phone'] ?? ''));
$street      = htmlspecialchars(trim($_POST['street'] ?? ''));
$city        = htmlspecialchars(trim($_POST['city'] ?? ''));
$state       = htmlspecialchars(trim($_POST['state'] ?? ''));
$zip         = htmlspecialchars(trim($_POST['zip'] ?? ''));

// ── Validate required fields ──
if (!$product || !$size || !$firstName || !$lastName || !$email || !$phone || !$street || !$city || !$state || !$zip) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── Build values ──
$fullName    = "$firstName $lastName";
$fullAddress = "$street, $city, $state $zip";
$orderDate   = date('F j, Y \a\t g:i A');
$orderRef    = 'CL-' . strtoupper(substr(md5(uniqid()), 0, 8));
$priceNum    = floatval(str_replace(['$', ','], '', $price));
$totalPrice  = '$' . number_format($priceNum * $quantity, 2);
$unitPrice   = '$' . number_format($priceNum, 2);

$fromEmail  = 'order@claritylabsbio.com';
$fromName   = 'Clarity Labs USA';
$adminEmail = 'johnathan.mericamarketing@gmail.com';

// ── Find COA PDF ──
$coaPdfPath = '';
$coaPdfName = '';
if ($productSlug) {
    $pdfDir = __DIR__ . '/../images/products/' . $productSlug . '/pdf/';
    if (is_dir($pdfDir)) {
        foreach (scandir($pdfDir) as $f) {
            if ($f === '.' || $f === '..') continue;
            if (preg_match('/\.pdf$/i', $f)) {
                $coaPdfPath = realpath($pdfDir . $f);
                $coaPdfName = $f;
                break;
            }
        }
    }
}

// ── Find product image for email (check_ image or 800px) ──
$productImgCid = '';
$productImgPath = '';
$productImgName = '';
if ($productSlug) {
    $imgDir = __DIR__ . '/../images/products/' . $productSlug . '/images/';
    if (is_dir($imgDir)) {
        // Try check_ image first
        foreach (scandir($imgDir) as $f) {
            if (stripos($f, 'check_') === 0 && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) {
                $productImgPath = realpath($imgDir . $f);
                $productImgName = $f;
                break;
            }
        }
        // Fallback to 800px
        if (!$productImgPath) {
            foreach (scandir($imgDir) as $f) {
                if (stripos($f, '800') !== false && preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) {
                    $productImgPath = realpath($imgDir . $f);
                    $productImgName = $f;
                    break;
                }
            }
        }
    }
}

// ── Determine image MIME type ──
function getImageMime($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    return $map[$ext] ?? 'application/octet-stream';
}

// ── Build MIME boundary ──
$boundary = md5(uniqid(time()));
$productImgCid = 'product-image-' . $boundary;


// ─────────────────────────────────────────────
// CUSTOMER EMAIL — HTML Body
// ─────────────────────────────────────────────
$customerSubject = "Order Confirmation — $product ($size) | Clarity Labs USA";

// Product image block (inline CID if available, otherwise skip)
$productImgHtml = '';
if ($productImgPath && file_exists($productImgPath)) {
    $productImgHtml = '
      <!-- Product Image -->
      <tr>
        <td style="padding:0 32px 8px; text-align:center;">
          <img src="cid:' . $productImgCid . '" alt="' . $product . '" width="140" style="width:140px; height:auto;">
        </td>
      </tr>';
}

$customerHtml = '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0; padding:0; background-color:#f4f5f7; -webkit-font-smoothing:antialiased;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="font-family:Arial,Helvetica,sans-serif; max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08);">

  <!-- ═══ HEADER ═══ -->
  <tr>
    <td style="background-color:#0B1E3F; padding:32px 32px 28px; text-align:center;">
      <h1 style="margin:0; font-size:24px; color:#ffffff; font-weight:700; letter-spacing:2px;">CLARITY LABS USA</h1>
      <p style="margin:6px 0 0; font-size:11px; color:#9BA3B5; letter-spacing:3px; text-transform:uppercase;">Research-Grade Peptides</p>
    </td>
  </tr>
  <tr><td style="height:4px; background:linear-gradient(90deg, #2A9D8F, #1A7A6E);"></td></tr>

  <!-- ═══ ORDER CONFIRMED BANNER ═══ -->
  <tr>
    <td style="padding:28px 32px 0; text-align:center;">
      <div style="display:inline-block; background-color:#EDF6F5; border:1px solid #C8E8E4; border-radius:50%; width:56px; height:56px; line-height:56px; font-size:28px; color:#1A7A6E; margin-bottom:12px;">&#10003;</div>
      <h2 style="margin:0 0 4px; font-size:22px; color:#0B1E3F;">Order Received, ' . $firstName . '!</h2>
      <p style="margin:0 0 0; font-size:14px; color:#6B7185; line-height:1.5;">We\'ve received your order and will be in touch shortly.</p>
    </td>
  </tr>

  <!-- ═══ ORDER REFERENCE BAR ═══ -->
  <tr>
    <td style="padding:20px 32px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8F9FA; border-radius:6px;">
        <tr>
          <td style="padding:12px 20px;">
            <span style="font-size:10px; color:#9BA3B5; text-transform:uppercase; letter-spacing:2px;">Order Ref</span><br>
            <span style="font-size:16px; color:#0B1E3F; font-weight:700; letter-spacing:1px;">' . $orderRef . '</span>
          </td>
          <td style="padding:12px 20px; text-align:right;">
            <span style="font-size:10px; color:#9BA3B5; text-transform:uppercase; letter-spacing:2px;">Date</span><br>
            <span style="font-size:13px; color:#0B1E3F;">' . $orderDate . '</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ PRODUCT CARD (like Step 1) ═══ -->
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0B1E3F; border-radius:8px; overflow:hidden;">
        <tr>
          ' . ($productImgPath && file_exists($productImgPath) ? '
          <td width="130" style="padding:20px; vertical-align:middle; text-align:center;">
            <img src="cid:' . $productImgCid . '" alt="' . $product . '" width="100" style="width:100px; height:auto;">
          </td>' : '') . '
          <td style="padding:20px ' . ($productImgPath ? '20px 20px 0' : '20px') . '; vertical-align:middle;">
            <h3 style="margin:0 0 4px; font-size:20px; color:#ffffff; font-weight:700;">' . $product . '</h3>
            <p style="margin:0 0 8px; font-size:10px; color:#9BA3B5; letter-spacing:2px; text-transform:uppercase;">Systemic Recovery Support Peptide</p>
            <span style="display:inline-block; background:rgba(42,157,143,0.15); color:#2A9D8F; font-size:11px; padding:4px 12px; border-radius:4px; letter-spacing:1px;">&#10003; Research Grade</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ ORDER DETAILS ═══ -->
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E6EB; border-radius:8px; overflow:hidden;">
        <tr>
          <td style="padding:14px 20px; background-color:#F8F9FA; border-bottom:1px solid #E4E6EB;">
            <span style="font-size:12px; font-weight:700; color:#0B1E3F; text-transform:uppercase; letter-spacing:2px;">Order Details</span>
          </td>
        </tr>
        <tr>
          <td style="padding:20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:8px 0; font-size:13px; color:#6B7185; border-bottom:1px solid #F4F5F7;">Product</td>
                <td style="padding:8px 0; font-size:13px; color:#0B1E3F; font-weight:600; text-align:right; border-bottom:1px solid #F4F5F7;">' . $product . '</td>
              </tr>
              <tr>
                <td style="padding:8px 0; font-size:13px; color:#6B7185; border-bottom:1px solid #F4F5F7;">Size</td>
                <td style="padding:8px 0; font-size:13px; color:#0B1E3F; font-weight:600; text-align:right; border-bottom:1px solid #F4F5F7;">' . $size . '</td>
              </tr>
              <tr>
                <td style="padding:8px 0; font-size:13px; color:#6B7185; border-bottom:1px solid #F4F5F7;">Price per Vial</td>
                <td style="padding:8px 0; font-size:13px; color:#0B1E3F; font-weight:600; text-align:right; border-bottom:1px solid #F4F5F7;">' . $unitPrice . '</td>
              </tr>
              <tr>
                <td style="padding:8px 0; font-size:13px; color:#6B7185; border-bottom:1px solid #F4F5F7;">Quantity</td>
                <td style="padding:8px 0; font-size:13px; color:#0B1E3F; font-weight:600; text-align:right; border-bottom:1px solid #F4F5F7;">' . $quantity . ' vial' . ($quantity > 1 ? 's' : '') . '</td>
              </tr>
              <tr>
                <td style="padding:12px 0 0; font-size:15px; color:#0B1E3F; font-weight:700;">Estimated Total</td>
                <td style="padding:12px 0 0; font-size:22px; color:#1A7A6E; font-weight:700; text-align:right;">' . $totalPrice . '</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ SHIPPING ADDRESS ═══ -->
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E6EB; border-radius:8px; overflow:hidden;">
        <tr>
          <td style="padding:14px 20px; background-color:#F8F9FA; border-bottom:1px solid #E4E6EB;">
            <span style="font-size:12px; font-weight:700; color:#0B1E3F; text-transform:uppercase; letter-spacing:2px;">Ship To</span>
          </td>
        </tr>
        <tr>
          <td style="padding:20px;">
            <p style="margin:0; font-size:14px; color:#0B1E3F; line-height:1.8; font-weight:500;">
              ' . $fullName . '<br>
              <span style="font-weight:400; color:#6B7185;">' . $street . '<br>
              ' . $city . ', ' . $state . ' ' . $zip . '</span>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ COA NOTICE ═══ -->
  ' . ($coaPdfPath ? '
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#EDF6F5; border:1px solid #C8E8E4; border-radius:8px;">
        <tr>
          <td style="padding:18px 20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="vertical-align:middle;">
                  <span style="font-size:20px; margin-right:8px;">&#128196;</span>
                  <span style="font-size:13px; color:#0B1E3F; font-weight:700;">Certificate of Analysis Attached</span><br>
                  <span style="font-size:12px; color:#6B7185; padding-left:32px;">Your COA for ' . $product . ' is attached to this email as a PDF.</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>' : '') . '

  <!-- ═══ WHAT HAPPENS NEXT ═══ -->
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFFBF0; border:1px solid #F0E6C8; border-radius:8px;">
        <tr>
          <td style="padding:20px;">
            <h3 style="margin:0 0 10px; font-size:14px; color:#0B1E3F; font-weight:700;">What Happens Next?</h3>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="28" style="vertical-align:top; padding:4px 0;">
                  <div style="width:22px; height:22px; border-radius:50%; background-color:#0B1E3F; color:#fff; text-align:center; line-height:22px; font-size:11px; font-weight:700;">1</div>
                </td>
                <td style="padding:4px 0 10px 8px; font-size:13px; color:#6B7185; line-height:1.5;">Our team reviews your order and verifies availability.</td>
              </tr>
              <tr>
                <td width="28" style="vertical-align:top; padding:4px 0;">
                  <div style="width:22px; height:22px; border-radius:50%; background-color:#0B1E3F; color:#fff; text-align:center; line-height:22px; font-size:11px; font-weight:700;">2</div>
                </td>
                <td style="padding:4px 0 10px 8px; font-size:13px; color:#6B7185; line-height:1.5;">You\'ll receive a follow-up to confirm details and arrange payment.</td>
              </tr>
              <tr>
                <td width="28" style="vertical-align:top; padding:4px 0;">
                  <div style="width:22px; height:22px; border-radius:50%; background-color:#1A7A6E; color:#fff; text-align:center; line-height:22px; font-size:11px; font-weight:700;">3</div>
                </td>
                <td style="padding:4px 0 0 8px; font-size:13px; color:#6B7185; line-height:1.5;">Once confirmed, your order ships with tracking provided.</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ CONTACT ═══ -->
  <tr>
    <td style="padding:0 32px 28px; text-align:center;">
      <p style="margin:0 0 8px; font-size:13px; color:#6B7185;">Questions about your order?</p>
      <a href="mailto:order@claritylabsbio.com" style="display:inline-block; background-color:#0B1E3F; color:#ffffff; font-size:13px; font-weight:600; padding:12px 32px; border-radius:6px; text-decoration:none; letter-spacing:0.5px;">Contact Us</a>
    </td>
  </tr>

  <!-- ═══ TRUST BADGES ═══ -->
  <tr>
    <td style="padding:0 32px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="33%" style="text-align:center; padding:8px;">
            <span style="font-size:16px; color:#2A9D8F;">&#10003;</span><br>
            <span style="font-size:10px; color:#9BA3B5; text-transform:uppercase; letter-spacing:1px;">Third-Party Tested</span>
          </td>
          <td width="33%" style="text-align:center; padding:8px;">
            <span style="font-size:16px; color:#2A9D8F;">&#10003;</span><br>
            <span style="font-size:10px; color:#9BA3B5; text-transform:uppercase; letter-spacing:1px;">COA Available</span>
          </td>
          <td width="33%" style="text-align:center; padding:8px;">
            <span style="font-size:16px; color:#2A9D8F;">&#10003;</span><br>
            <span style="font-size:10px; color:#9BA3B5; text-transform:uppercase; letter-spacing:1px;">US Shipping</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ═══ FOOTER ═══ -->
  <tr>
    <td style="background-color:#0B1E3F; padding:24px 32px; text-align:center;">
      <p style="margin:0 0 4px; font-size:13px; color:#ffffff; font-weight:600; letter-spacing:1px;">CLARITY LABS USA</p>
      <p style="margin:0 0 8px; font-size:11px; color:#9BA3B5;">For research and laboratory use only.</p>
      <p style="margin:0; font-size:10px; color:#6B7185;">&copy; ' . date('Y') . ' Clarity Labs USA. All rights reserved.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';


// ─────────────────────────────────────────────
// ADMIN EMAIL — HTML Body
// ─────────────────────────────────────────────
$adminSubject = "New Order — $product ($size) x$quantity — $fullName";

$adminHtml = '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f4f5f7;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="font-family:Arial,Helvetica,sans-serif; max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:8px; overflow:hidden;">

  <!-- Header -->
  <tr>
    <td style="background-color:#0B1E3F; padding:24px 32px;">
      <h1 style="margin:0; font-size:18px; color:#ffffff; font-weight:700; letter-spacing:1px;">NEW ORDER RECEIVED</h1>
      <p style="margin:4px 0 0; font-size:11px; color:#2A9D8F; letter-spacing:1px;">' . $orderDate . ' &bull; ' . $orderRef . '</p>
    </td>
  </tr>
  <tr><td style="height:4px; background:linear-gradient(90deg, #2A9D8F, #1A7A6E);"></td></tr>

  <!-- Body -->
  <tr>
    <td style="padding:28px 32px 24px;">

      <!-- Quick Glance -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
        <tr>
          <td width="33%" style="padding:12px; background-color:#EDF6F5; border-radius:6px; text-align:center;">
            <span style="font-size:10px; color:#6B7185; text-transform:uppercase; letter-spacing:1px; display:block;">Product</span>
            <span style="font-size:15px; color:#0B1E3F; font-weight:700; display:block; margin-top:4px;">' . $product . '</span>
          </td>
          <td width="4"></td>
          <td width="33%" style="padding:12px; background-color:#EDF6F5; border-radius:6px; text-align:center;">
            <span style="font-size:10px; color:#6B7185; text-transform:uppercase; letter-spacing:1px; display:block;">Size / Qty</span>
            <span style="font-size:15px; color:#0B1E3F; font-weight:700; display:block; margin-top:4px;">' . $size . ' &times; ' . $quantity . '</span>
          </td>
          <td width="4"></td>
          <td width="33%" style="padding:12px; background-color:#EDF6F5; border-radius:6px; text-align:center;">
            <span style="font-size:10px; color:#6B7185; text-transform:uppercase; letter-spacing:1px; display:block;">Est. Total</span>
            <span style="font-size:15px; color:#1A7A6E; font-weight:700; display:block; margin-top:4px;">' . $totalPrice . '</span>
          </td>
        </tr>
      </table>

      <!-- Customer Details -->
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E6EB; border-radius:6px; margin-bottom:20px;">
        <tr>
          <td style="padding:14px 20px; background-color:#F8F9FA; border-bottom:1px solid #E4E6EB;">
            <span style="font-size:12px; font-weight:700; color:#0B1E3F; text-transform:uppercase; letter-spacing:2px;">Customer</span>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:4px 0; font-size:13px; color:#6B7185; width:80px;">Name</td>
                <td style="padding:4px 0; font-size:13px; color:#0B1E3F; font-weight:600;">' . $fullName . '</td>
              </tr>
              <tr>
                <td style="padding:4px 0; font-size:13px; color:#6B7185;">Email</td>
                <td style="padding:4px 0; font-size:13px;"><a href="mailto:' . $email . '" style="color:#1A7A6E; text-decoration:none; font-weight:600;">' . $email . '</a></td>
              </tr>
              <tr>
                <td style="padding:4px 0; font-size:13px; color:#6B7185;">Phone</td>
                <td style="padding:4px 0; font-size:13px;"><a href="tel:' . $phone . '" style="color:#1A7A6E; text-decoration:none; font-weight:600;">' . $phone . '</a></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <!-- Shipping -->
      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E4E6EB; border-radius:6px; margin-bottom:20px;">
        <tr>
          <td style="padding:14px 20px; background-color:#F8F9FA; border-bottom:1px solid #E4E6EB;">
            <span style="font-size:12px; font-weight:700; color:#0B1E3F; text-transform:uppercase; letter-spacing:2px;">Ship To</span>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 20px;">
            <p style="margin:0; font-size:14px; color:#0B1E3F; line-height:1.7;">
              ' . $fullName . '<br>
              ' . $street . '<br>
              ' . $city . ', ' . $state . ' ' . $zip . '
            </p>
          </td>
        </tr>
      </table>

      <!-- Action button -->
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:8px 0;">
            <a href="mailto:' . $email . '?subject=Re: Your Clarity Labs USA Order ' . $orderRef . '" style="display:inline-block; background-color:#0B1E3F; color:#ffffff; font-size:13px; font-weight:600; padding:12px 28px; border-radius:6px; text-decoration:none; letter-spacing:0.5px;">Reply to Customer</a>
          </td>
        </tr>
      </table>

    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background-color:#F8F9FA; padding:20px 32px; border-top:1px solid #E4E6EB; text-align:center;">
      <p style="margin:0; font-size:11px; color:#9BA3B5;">Automated order notification &mdash; ClarityLabsUSA.com</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>';


// ═══════════════════════════════════════════════
// SEND EMAILS
// ═══════════════════════════════════════════════

// ── Helper: send multipart email with optional attachments & inline images ──
function sendMimeEmail($to, $subject, $htmlBody, $from, $fromName, $replyTo, $attachments = [], $inlineImages = []) {
    $boundary     = md5(uniqid(time()));
    $boundaryAlt  = md5('alt' . uniqid(time()));
    $boundaryRel  = md5('rel' . uniqid(time()));

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "From: $fromName <$from>\r\n";
    $headers .= "Reply-To: $replyTo\r\n";

    $hasAttachments = !empty($attachments);
    $hasInline      = !empty($inlineImages);

    if ($hasAttachments || $hasInline) {
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $body = "--$boundary\r\n";

        if ($hasInline) {
            // Related part (HTML + inline images)
            $body .= "Content-Type: multipart/related; boundary=\"$boundaryRel\"\r\n\r\n";
            $body .= "--$boundaryRel\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";

            // Inline images
            foreach ($inlineImages as $img) {
                $imgData = file_get_contents($img['path']);
                $body .= "--$boundaryRel\r\n";
                $body .= "Content-Type: " . $img['mime'] . "; name=\"" . $img['name'] . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-ID: <" . $img['cid'] . ">\r\n";
                $body .= "Content-Disposition: inline; filename=\"" . $img['name'] . "\"\r\n\r\n";
                $body .= chunk_split(base64_encode($imgData)) . "\r\n";
            }
            $body .= "--$boundaryRel--\r\n\r\n";
        } else {
            // Just HTML
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
        }

        // Attachments
        foreach ($attachments as $att) {
            $attData = file_get_contents($att['path']);
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/pdf; name=\"" . $att['name'] . "\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"" . $att['name'] . "\"\r\n\r\n";
            $body .= chunk_split(base64_encode($attData)) . "\r\n";
        }
        $body .= "--$boundary--\r\n";
    } else {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body = $htmlBody;
    }

    return mail($to, $subject, $body, $headers);
}

// ── Build attachments array ──
$attachments = [];
if ($coaPdfPath && file_exists($coaPdfPath)) {
    $attachments[] = [
        'path' => $coaPdfPath,
        'name' => 'COA_' . str_replace('-', '_', $productSlug) . '.pdf'
    ];
}

// ── Build inline images array ──
$inlineImages = [];
if ($productImgPath && file_exists($productImgPath)) {
    $inlineImages[] = [
        'path' => $productImgPath,
        'name' => $productImgName,
        'cid'  => $productImgCid,
        'mime' => getImageMime($productImgName)
    ];
}

// 1) Customer confirmation (with COA attachment + inline product image)
$customerSent = sendMimeEmail(
    $email,
    $customerSubject,
    $customerHtml,
    $fromEmail,
    $fromName,
    $fromEmail,
    $attachments,
    $inlineImages
);

// 2) Admin notification (Reply-To = customer email, no attachments)
$adminSent = sendMimeEmail(
    $adminEmail,
    $adminSubject,
    $adminHtml,
    $fromEmail,
    $fromName,
    "$fullName <$email>"
);

// ── JSON Response ──
if ($customerSent && $adminSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Order received! Check your email for confirmation.'
    ]);
} elseif ($customerSent || $adminSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Order received. We will be in touch shortly.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'There was an issue sending the confirmation. Please try again or contact us directly.'
    ]);
}
