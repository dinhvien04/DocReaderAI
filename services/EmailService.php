<?php
/**
 * Email Service
 * Handles email sending using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    /**
     * Configure PHPMailer with SMTP settings
     */
    private function configureMailer(): void {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USER'] ?? '';
            $this->mailer->Password = $_ENV['SMTP_PASS'] ?? '';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;
            
            // Sender info
            $this->mailer->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@docreader.com',
                $_ENV['SMTP_FROM_NAME'] ?? 'DocReader AI Studio'
            );
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Error configuring mailer: " . $e->getMessage());
        }
    }
    
    /**
     * Send OTP email
     * @param string $email Recipient email
     * @param string $otp OTP code
     * @param string $type Type of OTP (registration, reset)
     * @return bool
     */
    public function sendOtpEmail(string $email, string $otp, string $type = 'registration'): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            
            if ($type === 'registration') {
                $this->mailer->Subject = 'Xác thực tài khoản - DocReader AI Studio';
                $body = $this->getEmailTemplate('registration_otp', [
                    'otp' => $otp,
                    'expiry_minutes' => OTP_EXPIRY_MINUTES
                ]);
            } else {
                $this->mailer->Subject = 'Đặt lại mật khẩu - DocReader AI Studio';
                $body = $this->getEmailTemplate('reset_otp', [
                    'otp' => $otp,
                    'expiry_minutes' => OTP_EXPIRY_MINUTES
                ]);
            }
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error sending OTP email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send welcome email
     * @param string $email Recipient email
     * @return bool
     */
    public function sendWelcomeEmail(string $email): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            
            $this->mailer->Subject = 'Chào mừng đến với DocReader AI Studio';
            $body = $this->getEmailTemplate('welcome', ['email' => $email]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error sending welcome email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email template
     * @param string $type Template type
     * @param array $data Template data
     * @return string HTML email content
     */
    private function getEmailTemplate(string $type, array $data): string {
        $baseUrl = BASE_URL;
        $appName = $_ENV['APP_NAME'] ?? 'DocReader AI Studio';
        
        $commonStyles = "
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; color: #667eea; margin: 20px 0; border-radius: 8px; letter-spacing: 8px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        ";
        
        switch ($type) {
            case 'registration_otp':
                return "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        {$commonStyles}
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>{$appName}</h1>
                                <p>Xác thực tài khoản của bạn</p>
                            </div>
                            <div class='content'>
                                <h2>Chào mừng bạn!</h2>
                                <p>Cảm ơn bạn đã đăng ký tài khoản tại {$appName}. Để hoàn tất quá trình đăng ký, vui lòng sử dụng mã OTP bên dưới:</p>
                                <div class='otp-box'>{$data['otp']}</div>
                                <p><strong>Lưu ý:</strong> Mã OTP này sẽ hết hạn sau {$data['expiry_minutes']} phút.</p>
                                <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; 2024 {$appName}. All rights reserved.</p>
                                <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
            case 'reset_otp':
                return "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        {$commonStyles}
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>{$appName}</h1>
                                <p>Đặt lại mật khẩu</p>
                            </div>
                            <div class='content'>
                                <h2>Yêu cầu đặt lại mật khẩu</h2>
                                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Vui lòng sử dụng mã OTP bên dưới:</p>
                                <div class='otp-box'>{$data['otp']}</div>
                                <p><strong>Lưu ý:</strong> Mã OTP này sẽ hết hạn sau {$data['expiry_minutes']} phút.</p>
                                <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này và mật khẩu của bạn sẽ không bị thay đổi.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; 2024 {$appName}. All rights reserved.</p>
                                <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
            case 'welcome':
                return "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        {$commonStyles}
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>{$appName}</h1>
                                <p>Chào mừng bạn đến với chúng tôi!</p>
                            </div>
                            <div class='content'>
                                <h2>Xin chào!</h2>
                                <p>Tài khoản của bạn đã được kích hoạt thành công. Bạn có thể bắt đầu sử dụng các tính năng của {$appName}:</p>
                                <ul>
                                    <li>🎙️ Chuyển đổi văn bản thành giọng nói với 6 giọng đọc tiếng Việt</li>
                                    <li>📄 Upload và xử lý tài liệu PDF, TXT</li>
                                    <li>🌐 Dịch thuật đa ngôn ngữ</li>
                                    <li>📝 Tóm tắt văn bản thông minh</li>
                                    <li>📊 Quản lý lịch sử audio của bạn</li>
                                </ul>
                                <a href='{$baseUrl}/dashboard' class='button'>Bắt đầu sử dụng</a>
                                <p>Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; 2024 {$appName}. All rights reserved.</p>
                                <p>Email: support@docreader.com</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
            default:
                return '';
        }
    }
}
