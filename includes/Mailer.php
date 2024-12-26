<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        
        return $mail;
    }

    public static function sendPasswordReset($email, $token) {
        try {
            $mail = self::getMailer();
            $mail->addAddress($email);
            
            $resetLink = BASE_URL . '/reset_password.php?token=' . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>If you didn't request this, please ignore this email.</p>
                <p>This link will expire in 1 hour.</p>
            ";
            
            $mail->send();
            Logger::info('Password reset email sent to: ' . $email);
            return true;
        } catch (Exception $e) {
            Logger::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendBookingConfirmation($email, $bookingDetails) {
        try {
            $mail = self::getMailer();
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmation';
            $mail->Body = "
                <h2>Booking Confirmation</h2>
                <p>Thank you for your booking!</p>
                <h3>Booking Details:</h3>
                <ul>
                    <li>Hotel: {$bookingDetails['hotel_name']}</li>
                    <li>Room: {$bookingDetails['room_type']}</li>
                    <li>Check-in: {$bookingDetails['check_in']}</li>
                    <li>Check-out: {$bookingDetails['check_out']}</li>
                    <li>Total Price: {$bookingDetails['total_price']}</li>
                </ul>
            ";
            
            $mail->send();
            Logger::info('Booking confirmation email sent to: ' . $email);
            return true;
        } catch (Exception $e) {
            Logger::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }
}
