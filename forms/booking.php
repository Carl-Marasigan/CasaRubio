<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ DB CONNECTION
include("../connection_string/connect-db.php");

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ✅ Get form data
  $name = htmlspecialchars(trim($_POST["name"] ?? ''));
  $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
  $contact = htmlspecialchars(trim($_POST["contact"] ?? ''));
  $trans = 'TR-' . date("md") . rand(1000,9999);
  $arrival_date = $_POST["arrival_date"] ?? '';
  $departure_date = $_POST["departure_date"] ?? '';
  $arrival_time = $_POST["arrival_time"] ?? '';
  $departure_time = $_POST["departure_time"] ?? '';
  $stay_type_input = $_POST["stay_type"] ?? '';
  $guests = $_POST["guests"] ?? '';
  $requirements = htmlspecialchars(trim($_POST["requirements"] ?? ''));
  $status = 0;

  // ✅ Map stay type to exact database value
  $stay_type_map = [
      "overnight" => "Overnight",
      "daytour"   => "Daytour",
      "22hour"    => "22 Hour"
  ];
  $stay_type = $stay_type_map[$stay_type_input] ?? 'Overnight'; // default to Overnight

  // ✅ Validation
  if (!$name || !$email || !$arrival_date || !$departure_date || !$arrival_time || !$departure_time || !$stay_type) {
    echo "Error: Please fill all required fields.";
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email.";
    exit;
  }

  // ✅ INSERT INTO DATABASE
  $stmt = $conn->prepare("INSERT INTO booking 
    (name, email, contact, arrival_date, departure_date, arrival_time, departure_time, stay_type, guests, requirements, status, trans) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->bind_param(
    "ssssssssisis",
    $name,
    $email,
    $contact,
    $arrival_date,
    $departure_date,
    $arrival_time,
    $departure_time,
    $stay_type,
    $guests,
    $requirements,
    $status,
    $trans
  );

  if (!$stmt->execute()) {
    echo "Error: " . $stmt->error;
    exit;
  }

  // ✅ EMAIL SETUP
  $mail = new PHPMailer(true);

  try {

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kpgrptest01@gmail.com';
    $mail->Password = 'hrmxstkzinnosolj';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom("kpgrptest01@gmail.com", "Casa Rubio Booking");
    $mail->addReplyTo($email, $name);
    $mail->addAddress("carlzmarasiganz@gmail.com");

    $mail->isHTML(true);
    $mail->Subject = "New Booking Request - Casa Rubio";

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <body style='margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;'>

    <table width='100%' cellpadding='0' cellspacing='0' style='padding:30px 0;'>
      <tr>
        <td align='center'>

          <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:12px; overflow:hidden;'>

            <!-- HEADER -->
            <tr>
              <td style='background:#1e88e5; padding:25px; text-align:center;'>
                <h2 style='color:#fff; margin:0;'>Casa Rubio Private Resort</h2>
                <p style='color:#dbe7ff; margin:5px 0 0; font-size:13px;'>
                  New Booking Request
                </p>
              </td>
            </tr>

            <!-- BODY -->
            <tr>
              <td style='padding:30px;'>

                <h3 style='color:#1e88e5;'>Guest Information</h3>
                <table width='100%' cellpadding='8'>
                  <tr><td><b>Name</b></td><td>$name</td></tr>
                  <tr><td><b>Email</b></td><td>$email</td></tr>
                  <tr><td><b>Contact</b></td><td>$contact</td></tr>
                </table>

                <br>

                <h3 style='color:#1e88e5;'>Booking Details</h3>
                <table width='100%' cellpadding='8'>
                  <tr><td><b>Stay Type</b></td><td>$stay_type</td></tr>
                  <tr><td><b>Arrival Date & Time</b></td><td>$arrival_date $arrival_time</td></tr>
                  <tr><td><b>Departure Date & Time</b></td><td>$departure_date $departure_time</td></tr>
                  <tr><td><b>Total Guests</b></td><td>$guests</td></tr>
                </table>

                <br>

                <h3 style='color:#1e88e5;'>Additional Request</h3>
                <table width='100%' cellpadding='8'>
                  <tr><td>$requirements</td></tr>
                </table>

                <br>

                <div style='padding:12px; background:#e3f2fd; border-left:5px solid #1e88e5;'>
                  Reservation request — please confirm availability.
                </div>

              </td>
            </tr>

            <!-- FOOTER -->
            <tr>
              <td style='background:#f1f3f6; text-align:center; padding:15px; font-size:12px; color:#666;'>
                © " . date("Y") . " Casa Rubio Private Resort
              </td>
            </tr>

          </table>

        </td>
      </tr>
    </table>

    </body>
    </html>
    ";

    $mail->send();

    echo "OK";

  } catch (Exception $e) {
    echo "Error: Email failed.";
  }
}
?>