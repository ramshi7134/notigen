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

    public function renderContent(array $data)
    {
        $template = str_replace(['{', '}'], ['{{ ', ' }}'], $this->content);
        return view('notigen::string-template', ['template' => $template])
            ->with($data)
            ->render();
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
