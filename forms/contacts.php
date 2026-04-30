<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ✅ Sanitize inputs
  $name = htmlspecialchars(trim($_POST["name"] ?? ''));
  $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
  $subject = htmlspecialchars(trim($_POST["subject"] ?? ''));
  $message = htmlspecialchars(trim($_POST["message"] ?? ''));
  $recaptcha = $_POST["g-recaptcha-response"] ?? '';

  // ✅ Validation
  if (!$name || !$email || !$subject || !$message) {
    echo "Error: All fields are required.";
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email format.";
    exit;
  }

  if (empty($recaptcha)) {
    echo "Error: Please complete the captcha verification.";
    exit;
  }

  // ✅ reCAPTCHA verification (cURL - safer for hosting)
  $secretKey = "6LebvdMpAAAAADipY3AePOrP6KtZpuGKmqaNXUMe";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret' => $secretKey,
    'response' => $recaptcha
  ]));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  curl_close($ch);

  $captchaSuccess = json_decode($response);

  if (!$captchaSuccess || !$captchaSuccess->success) {
    echo "Error: Captcha verification failed.";
    exit;
  }

  // ✅ Send Email
  $mail = new PHPMailer(true);

  try {

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // ⚠️ Replace with your credentials or ENV variables
    $mail->Username = 'kpgrptest01@gmail.com';
    $mail->Password = 'hrmxstkzinnosolj';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Sender / Recipient
    $mail->setFrom("kpgrptest01@gmail.com", "Casa Rubio Contact Form");
    $mail->addReplyTo($email, $name);
    $mail->addAddress("carlzmarasiganz@gmail.com");

    // ✅ EMBED LOGO
    // $mail->addEmbeddedImage('assets/resort-img/casa-logo.png', 'logo_casa');


    // Email Content
    $mail->isHTML(true);
    $mail->Subject = "Casa Rubio Inquiry: $subject";

    $mail->Body = "
        <!DOCTYPE html>
        <html>
        <body style='margin:0; padding:0; background:#f4f6f9;'>

          <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f9; padding:30px 0;'>
            <tr>
              <td align='center'>

                <table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);'>

                  <!-- HEADER -->
                  <tr>
                    <td align='center' style='background:#0d6efd; padding:25px;'>
                      <img src='https://scontent.fceb2-2.fna.fbcdn.net/v/t39.30808-6/423237249_122132123948191689_3659319020584668391_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=1d70fc&_nc_eui2=AeF8ApGJ1Kyb5PkvdAyU7mpygtBuelTqPbiC0G56VOo9uP1Ec8jcLEbrtL5bmSsEJGw2ocQeYB8atENxmWlcAlER&_nc_ohc=Fc5EJviscz8Q7kNvwHbkJ3O&_nc_oc=AdoEALEtuPPpDKDxJblLkA7ASy353G1fPhK0U7UFb3IzyqIwUxI5T3D6YZaPR0Buo5M&_nc_zt=23&_nc_ht=scontent.fceb2-2.fna&_nc_gid=Fd0TVh5b5Q5InWPFRAr6hQ&_nc_ss=7a3a8&oh=00_Af3U5hnGxbhcE76ngMqxlrb1fL3Qooe0rAR_WEfQypQpjQ&oe=69EBC38A' alt='KPG Logo' style='max-height: 40px;'>
                      <h2 style='color:#fff; margin:0; font-family:Arial;'>Casa Rubio Resort</h2>
                      <p style='color:#dbe7ff; margin:5px 0 0; font-family:Arial; font-size:13px;'>
                        New Contact Inquiry
                      </p>
                    </td>
                  </tr>

                  <!-- BODY -->
                  <tr>
                    <td style='padding:30px; font-family:Arial; color:#333;'>

                      <h3 style='margin-top:0; color:#0d6efd;'>Contact Details</h3>

                      <table width='100%' cellpadding='8' style='font-size:14px;'>
                        <tr>
                          <td><b>Name:</b></td>
                          <td>$name</td>
                        </tr>
                        <tr>
                          <td><b>Email:</b></td>
                          <td>$email</td>
                        </tr>
                        <tr>
                          <td><b>Subject:</b></td>
                          <td>$subject</td>
                        </tr>
                      </table>

                      <hr style='margin:20px 0;'>

                      <h4 style='color:#0d6efd;'>Message</h4>
                      <p style='line-height:1.6; font-size:14px;'>
                        $message
                      </p>

                    </td>
                  </tr>

                  <!-- FOOTER -->
                  <tr>
                    <td style='background:#f1f3f6; text-align:center; padding:15px; font-size:12px; color:#666; font-family:Arial;'>
                      © " . date("Y") . " Casa Rubio Resort. All Rights Reserved.
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

    // ✅ IMPORTANT: return OK for success
    echo "OK";

  } catch (Exception $e) {
    echo "Error: Message could not be sent.";
  }
}
?>