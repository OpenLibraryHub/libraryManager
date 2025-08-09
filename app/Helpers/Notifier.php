<?php
namespace App\Helpers;

class Notifier {
    /**
     * Log an email-like notification to file (placeholder for SMTP)
     */
    public static function sendEmail(string $to, string $subject, string $body): void {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ];
        $logFile = LOGS_PATH . '/emails.log';
        error_log(json_encode($log) . PHP_EOL, 3, $logFile);
    }
}


