<?php

namespace Notigen\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'notification_templates';

    protected $fillable = [
        'name',
        'description',
        'channels',
        'content',
        'variables'
    ];

    protected $casts = [
        'channels' => 'array',
        'variables' => 'array'
    ];

    public function getFormattedContentAttribute()
    {
        return str_replace(['{', '}'], ['{{ ', ' }}'], $this->content);
    }

    protected function replaceVariables(string $content, array $data): string
    {
        return preg_replace_callback('/{{\\s*([\\w.]+)\\s*}}/', function ($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? '';
        }, $content);
    }

    public function renderContent(array $data)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Template rendering started', [
                'template_name' => $this->name,
                'content' => $this->content,
                'data' => $data
            ]);

            // First pass: Replace variables directly
            $renderedContent = $this->replaceVariables($this->content, $data);

            \Illuminate\Support\Facades\Log::info('Template rendered successfully', [
                'template_name' => $this->name,
                'rendered_content' => $renderedContent
            ]);

            return $renderedContent;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Template rendering failed', [
                'template_name' => $this->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send notification using this template
     *
     * @param mixed $notifiable The entity receiving the notification
     * @param array $data Template variables
     * @param array|null $channels Specific channels to use (defaults to template channels)
     * @return void
     */
    public function send($notifiable, array $data, ?array $channels = null)
    {
        $channels = $channels ?? $this->channels;
        $notifiable->notify(new \Notigen\Notifications\TemplateNotification(
            $this,
            $data,
            $channels
        ));
    }

    /**
     * Find template by name
     *
     * @param string $name
     * @return static|null
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }
}
