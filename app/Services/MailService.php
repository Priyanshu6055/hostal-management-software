<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Exception;

class MailService
{
    /**
     * Send email using AWS SES or default mailer
     */
    public static function send($to, $subject, $view, $data = [])
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            if (count(Mail::failures()) > 0) {
                return [
                    'success' => false,
                    'error' => 'Failed to send email.',
                    'details' => Mail::failures()
                ];
            }

            return [
                'success' => true,
                'message' => 'Mail sent successfully.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
