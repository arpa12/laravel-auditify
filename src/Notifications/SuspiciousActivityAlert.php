<?php

namespace Auditify\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class SuspiciousActivityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $log;
    public $reason;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $log
     * @param  string  $reason
     */
    public function __construct($log, string $reason)
    {
        $this->log = $log;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        $channels = config('auditify.alerts.channels', ['mail', 'log']);

        // Handle custom 'log' channel mapping manually
        if (in_array('log', $channels)) {
            Log::warning('Auditify Security Alert: ' . $this->reason, [
                'log_id' => $this->log->id,
                'title' => $this->log->title ?? ($this->log->action ?? 'Alert'),
                'description' => $this->log->description,
                'user_id' => $this->log->user_id,
                'timestamp' => $this->log->created_at,
            ]);
        }

        // Return channels Laravel natively supports
        return array_values(array_filter($channels, function ($channel) {
            return $channel !== 'log';
        }));
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $viewUrl = url(config('auditify.route_prefix', 'auditify') . '/security-logs/' . $this->log->id);

        return (new MailMessage)
            ->error()
            ->subject('⚠️ Auditify Security Alert: ' . ($this->log->title ?? 'Suspicious Activity Detected'))
            ->greeting('Security Alert!')
            ->line('Auditify has detected suspicious activity that requires administrator review.')
            ->line('**Reason:** ' . $this->reason)
            ->line('**Details:**')
            ->line('• **ID:** #' . $this->log->id)
            ->line('• **Title:** ' . ($this->log->title ?? 'N/A'))
            ->line('• **Description:** ' . $this->log->description)
            ->line('• **Severity:** ' . strtoupper($this->log->severity ?? 'medium'))
            ->line('• **Timestamp:** ' . $this->log->created_at->format('Y-m-d H:i:s'))
            ->action('View Security Log Details', $viewUrl)
            ->line('Please review your server security logs if this activity was not initiated by an authorized administrator.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'log_id' => $this->log->id,
            'reason' => $this->reason,
            'title' => $this->log->title ?? 'N/A',
            'severity' => $this->log->severity ?? 'medium',
        ];
    }
}
