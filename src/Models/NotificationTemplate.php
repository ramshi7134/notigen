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
}
