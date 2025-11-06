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
     * @param string $templateName
     * @param mixed $notifiable
     * @param array $data
     * @param array|null $channels
     * @return void
     */
    public function send(string $templateName, $notifiable, array $data, ?array $channels = null)
    {
        $template = $this->findTemplate($templateName);
        
        if ($template) {
            $template->send($notifiable, $data, $channels);
        }
    }

    /**
     * Find a template by name
     *
     * @param string $name
     * @return \Notigen\Models\NotificationTemplate|null
     */
    public function findTemplate(string $name)
    {
        return \Notigen\Models\NotificationTemplate::findByName($name);
    }
}
