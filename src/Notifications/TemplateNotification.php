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
        $this->channels = $channels;

        Log::info('TemplateNotification created', [
            'template' => $template->name,
            'data' => $data,
            'channels' => $channels
        ]);

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
            $renderedContent = $this->template->renderContent(['notifiable' => $notifiable] + $this->data);
            
            Log::info('TemplateNotification email being sent', [
                'to' => $notifiable->email ?? 'no_email_found',
                'template' => $this->template->name,
                'content' => $renderedContent,
                'subject' => $this->renderSubject(),
                'mailtrap_config' => [
                    'driver' => config('mail.driver'),
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'from' => config('mail.from'),
                ]
            ]);
            
            return (new MailMessage)
                ->subject($this->renderSubject())
                ->greeting('Hello ' . ($this->data['name'] ?? ''))
                ->line($renderedContent)
                ->salutation('Regards');
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
     * Render the notification subject
     */
    protected function renderSubject(): string
    {
        return $this->template->name;
    }
}
