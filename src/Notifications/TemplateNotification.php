<?php

namespace Notigen\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Notigen\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;

class TemplateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The notification template.
     *
     * @var \Notigen\Models\NotificationTemplate
     */
    protected $template;

    /**
     * The template data.
     *
     * @var array<string, mixed>
     */
    protected $data;

    /**
     * The notification channels.
     *
     * @var array<string>
     */
    protected $channels;

    /**
     * Create a new notification instance.
     *
     * @param \Notigen\Models\NotificationTemplate $template
     * @param array<string, mixed> $data
     * @param array<string>|null $channels
     */
    public function __construct(NotificationTemplate $template, array $data, ?array $channels = null)
    {
        $this->template = $template;
        $this->data = $data;
        $this->channels = $channels ?? $template->channels;

        // Set queue if configured
        if (config('notigen.queue_notifications', true)) {
            $this->onQueue(config('notigen.default_queue', 'default'));
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return array_intersect(
            $this->channels, 
            ['mail', 'database', 'slack']  // Supported channels
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // Add notifiable to data context
        $data = array_merge($this->data, [
            'notifiable' => $notifiable
        ]);

        // Prepare content and subject
        $content = $this->template->renderContent($data);
        $subject = $this->template->subject 
            ? $this->template->replaceVariables($this->template->subject, $data)
            : $this->template->name;

        // Log email preparation
        Log::info('Preparing email notification', [
            'template' => $this->template->template_key,
            'to' => $notifiable->email ?? 'no_email_found'
        ]);

        // Create mail message. Prefer MailMessage::html() when available
        // for Laravel versions that support it, otherwise fall back to
        // rendering a small wrapper view which echoes the HTML.
        $mail = new MailMessage();
        $mail->subject($subject);

        if (method_exists($mail, 'html')) {
            $mail->html($content);
        } else {
            $mail->view('notigen::templates.raw', [
                'html' => $content,
            ]);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $data = array_merge($this->data, [
            'notifiable' => $notifiable
        ]);

        return [
            'template_key' => $this->template->template_key,
            'content' => $this->template->renderContent($data),
            'data' => $data,
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable): SlackMessage
    {
        $data = array_merge($this->data, [
            'notifiable' => $notifiable
        ]);

        return (new SlackMessage)
            ->content($this->template->renderContent($data));
    }
}
