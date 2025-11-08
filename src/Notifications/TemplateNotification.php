<?php

namespace Notigen\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notigen\Models\NotificationTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class TemplateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var NotificationTemplate
     */
    protected $template;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(NotificationTemplate $template, array $data, array $channels)
    {
        $this->template = $template;
        $this->data = $data;
        $this->channels = array_filter($channels, function($channel) {
            // Only allow mail channel for now to avoid Slack webhook issues
            return $channel === 'mail';
        });

        try {
            Log::info('TemplateNotification created', [
                'template' => $template->name,
                'data' => $data,
                'channels' => $this->channels
            ]);
        } catch (\Exception $e) {
            // Fallback to error_log if Laravel logging fails
            error_log('TemplateNotification created: ' . json_encode([
                'template' => $template->name,
                'data' => $data,
                'channels' => $this->channels
            ]));
        }

        if (config('notigen.queue_notifications', true)) {
            $this->onQueue(config('notigen.default_queue', 'default'));
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        try {
            $data = ['notifiable' => $notifiable] + $this->data;
            
            try {
                error_log('TemplateNotification email preparation: ' . json_encode([
                    'to' => $notifiable->email ?? 'no_email_found',
                    'template' => $this->template->name,
                    'data' => $data
                ]));
            } catch (\Exception $e) {
                // Ignore logging errors
            }
            
            $message = new MailMessage;
            
            // Set and render subject with variables
            $renderedSubject = $this->renderSubject();
            $message->subject($renderedSubject);
            
            try {
                error_log('TemplateNotification subject rendered: ' . json_encode([
                    'subject_template' => $this->template->subject,
                    'rendered_subject' => $renderedSubject,
                    'data' => $this->data
                ]));
            } catch (\Exception $e) {
                // Ignore logging errors
            }
            
            // Get the rendered content and clean it up
            $renderedContent = $this->template->renderContent($data);
            $renderedContent = html_entity_decode(strip_tags($renderedContent));
            
            // Split content into lines and process each line
            $lines = explode("\n", $renderedContent);
            $lines = array_map('trim', $lines);
            
            // Remove empty lines from start and end
            while (!empty($lines) && empty($lines[0])) array_shift($lines);
            while (!empty($lines) && empty(end($lines))) array_pop($lines);
            
            // If there's a greeting line (starts with "Hi" or "Hello"), use it as greeting
            $firstLine = reset($lines);
            if (preg_match('/^(Hi|Hello)\b/i', $firstLine)) {
                $message->greeting($firstLine);
                array_shift($lines);
            }
            
            // Add each non-empty line as a separate line in the email
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $message->line($line);
                }
            }
            
            $message->salutation('Regards');
            
            return $message;
        } catch (\Exception $e) {
            Log::error('TemplateNotification email failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'template_id' => $this->template->id,
            'content' => $this->template->renderContent($this->data),
            'data' => $this->data,
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack($notifiable): array
    {
        return [
            'content' => $this->template->renderContent($this->data),
        ];
    }

    /**
     * Render the notification subject with variables
     */
    protected function renderSubject(): string
    {
        try {
            $subject = $this->template->subject ?? $this->template->name ?? 'Notification';
            
            // Use the same variable replacement for subject as content
            return $this->template->replaceVariables($subject, $this->data);
        } catch (\Exception $e) {
            error_log('Error rendering subject: ' . $e->getMessage());
            return 'Notification';
        }
    }
}
