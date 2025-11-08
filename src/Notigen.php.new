<?php

namespace Notigen;

use Illuminate\Contracts\Foundation\Application;

class Notigen
{
    /**
     * The Laravel application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * Create a new Notigen instance
     *
     * @param Application|null $app
     */
    public function __construct(Application $app = null)
    {
        if ($app) {
            $this->app = $app;
        }
        $this->templateManager = app(TemplateManager::class);
    }

    /**
     * Send a notification using a template
     *
     * @param string $uniqueKey The unique key of the template
     * @param mixed $notifiable The entity to receive the notification
     * @param array $data The data to use in the template
     * @param array|null $channels Optional specific channels to use
     * @return void
     * @throws \Exception When template is not found
     */
    public function send(string $uniqueKey, $notifiable, array $data, ?array $channels = null)
    {
        $template = $this->findTemplate($uniqueKey);
        
        if (!$template) {
            throw new \Exception("Template with key '{$uniqueKey}' not found.");
        }
        
        $template->send($notifiable, $data, $channels);
    }

    /**
     * Find a template by key or name
     *
     * @param string $key
     * @return \Notigen\Models\NotificationTemplate|null
     */
    protected function findTemplate(string $key)
    {
        $template = Models\NotificationTemplate::where('unique_key', $key)->first();
        
        if (!$template) {
            // Fallback to name for backward compatibility
            $template = Models\NotificationTemplate::where('name', $key)->first();
        }
        
        return $template;
    }
}
