<?php

namespace Notigen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'template_key',
        'description',
        'channels',
        'content',
        'subject',
        'variables',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'channels' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->template_key)) {
                $template->template_key = Str::slug($template->name) . '_' . Str::random(6);
            }
        });

        static::saved(function ($template) {
            Cache::forget("template:{$template->template_key}");
        });

        static::deleted(function ($template) {
            Cache::forget("template:{$template->template_key}");
        });
    }

    /**
     * Get the formatted content with proper Blade syntax.
     *
     * @return string
     */
    public function getFormattedContentAttribute(): string
    {
        return str_replace(['{', '}'], ['{{ ', ' }}'], $this->content);
    }

    /**
     * Replace variables in the content.
     *
     * @param string $content
     * @param array<string, mixed> $data
     * @return string
     */
    protected function replaceVariables(string $content, array $data): string
    {
        // Replace both {{name}} and {name} style variables
        return preg_replace_callback('/(?:{{\\s*([\\w.]+)\\s*}}|{([\\w.]+)})/', function ($matches) use ($data) {
            $key = $matches[1] ?? $matches[2];
            return data_get($data, $key, '');
        }, $content);
    }

    /**
     * Render the template content with the provided data.
     *
     * @param array<string, mixed> $data
     * @return string
     * @throws \Throwable
     */
    public function renderContent(array $data): string
    {
        $this->validateVariables($data);

        return Cache::remember("template:{$this->template_key}:render:" . md5(json_encode($data)), 
            config('notigen.cache.ttl', 3600), 
            function () use ($data) {
                try {
                    // First replace variables in content
                    $content = $this->replaceVariables($this->content, $data);

                    // Then render through Blade if there's a view
                    if (View::exists('notigen::templates.email')) {
                        return View::make('notigen::templates.email', [
                            'content' => $content,
                            'subject' => $this->replaceVariables($this->subject ?? '', $data),
                        ])->render();
                    }

                    return $content;
                } catch (\Throwable $e) {
                    report($e);
                    throw $e;
                }
            }
        );
    }

    /**
     * Validate the provided variables against the template requirements.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    protected function validateVariables(array $data): void
    {
        if (!$this->variables) {
            return;
        }

        $missingVars = collect($this->variables)
            ->where('required', true)
            ->pluck('name')
            ->filter(fn ($var) => !array_key_exists($var, $data))
            ->values();

        if ($missingVars->isNotEmpty()) {
            throw new \InvalidArgumentException(
                "Missing required variables: " . $missingVars->implode(', ')
            );
        }
    }

    /**
     * Send notification using this template.
     *
     * @param mixed $notifiable
     * @param array<string, mixed> $data
     * @param array<string>|null $channels
     * @return void
     * @throws \InvalidArgumentException
     */
    public function send($notifiable, array $data, ?array $channels = null): void
    {
        if (!$this->is_active) {
            throw new \InvalidArgumentException("Template {$this->template_key} is not active");
        }

        $notifiable->notify(new \Notigen\Notifications\TemplateNotification(
            $this,
            $data,
            $channels ?? $this->channels
        ));
    }

    /**
     * Find a template by its key.
     *
     * @param string $key
     * @return static|null
     */
    public static function findByKey(string $key)
    {
        return static::where('unique_key', $key)->first();
    }

    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }
}
