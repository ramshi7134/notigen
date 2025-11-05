<?php

namespace Notigen;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Notigen
{
    protected TemplateManager $templateManager;

    public function __construct()
    {
        $this->templateManager = new TemplateManager();
    }

    /**
     * Register a new notification template.
     *
     * @param string $name
     * @param array $channels
     * @param array $variables
     * @param string|null $view
     * @return string Template identifier
     */
    public function registerTemplate(string $name, array $channels = ['mail'], array $variables = [], ?string $view = null): string
    {
        return $this->templateManager->registerTemplate($name, $channels, $variables, $view);
    }

    /**
     * Create a new notification class with template support.
     *
     * @param string $name
     * @param string|null $templateId
     * @param array $channels
     * @return bool
     */
    public function createNotification(string $name, ?string $templateId = null, array $channels = ['mail']): bool
    {
        $notificationPath = app_path('Notifications/' . $name . '.php');
        
        // Create the notifications directory if it doesn't exist
        if (!File::isDirectory(app_path('Notifications'))) {
            File::makeDirectory(app_path('Notifications'), 0755, true);
        }

        // Generate notification content
        $stub = File::get(__DIR__.'/stubs/notification.stub');
        $content = $this->replaceStubContent($stub, $name, $channels, $templateId);

        // Create the notification file
        File::put($notificationPath, $content);

        return File::exists($notificationPath);
    }

    /**
     * Send a notification using a template.
     *
     * @param string $templateId
     * @param array $variables
     * @param mixed $notifiable
     * @return void
     */
    public function sendWithTemplate(string $templateId, array $variables, $notifiable): void
    {
        $this->templateManager->validateVariables($templateId, $variables);
        
        $template = $this->templateManager->getTemplate($templateId);
        if (!$template) {
            throw new \InvalidArgumentException("Template not found: {$templateId}");
        }

        $notification = $this->createNotificationFromTemplate($templateId, $variables);
        $notifiable->notify($notification);
    }

    /**
     * Replace the stub content with actual values.
     *
     * @param string $stub
     * @param string $name
     * @param array $channels
     * @param string|null $templateId
     * @return string
     */
    protected function replaceStubContent(string $stub, string $name, array $channels, ?string $templateId = null): string
    {
        $channelMethods = $this->generateChannelMethods($channels, $templateId);
        
        $replacements = [
            '{{namespace}}' => 'App\\Notifications',
            '{{class}}' => $name,
            '{{channels}}' => implode("', '", $channels),
            '{{channelMethods}}' => $channelMethods,
        ];

        if ($templateId) {
            $replacements['{{template_id}}'] = $templateId;
            $replacements['{{template_properties}}'] = $this->generateTemplateProperties($templateId);
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }

    /**
     * Generate channel methods for the notification.
     *
     * @param array $channels
     * @param string|null $templateId
     * @return string
     */
    protected function generateChannelMethods(array $channels, ?string $templateId = null): string
    {
        $methods = '';
        
        foreach ($channels as $channel) {
            $methodName = 'to' . Str::studly($channel);
            $methods .= $this->getChannelMethodStub($methodName, $channel, $templateId);
        }
        
        return $methods;
    }

    /**
     * Get the stub for a channel method.
     *
     * @param string $methodName
     * @param string $channel
     * @param string|null $templateId
     * @return string
     */
    protected function getChannelMethodStub(string $methodName, string $channel, ?string $templateId = null): string
    {
        if ($templateId) {
            switch ($channel) {
                case 'mail':
                    return $this->getTemplateMailMethodStub($methodName);
                case 'slack':
                    return $this->getTemplateSlackMethodStub($methodName);
                case 'sms':
                    return $this->getTemplateSmsMethodStub($methodName);
                default:
                    return $this->getTemplateDefaultMethodStub($methodName, $channel);
            }
        }

        return "
    /**
     * Get the {$channel} representation of the notification.
     *
     * @param mixed \$notifiable
     * @return array
     */
    public function {$methodName}(\$notifiable)
    {
        return [
            //
        ];
    }
";
    }

    /**
     * Get the stub for a template-based mail method.
     *
     * @param string $methodName
     * @return string
     */
    protected function getTemplateMailMethodStub(string $methodName): string
    {
        return "
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed \$notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function {$methodName}(\$notifiable)
    {
        \$content = \$this->template->getChannelContent('mail');
        
        return (new MailMessage)
            ->subject(\$this->replaceVariables(\$content['subject']))
            ->markdown('notigen::mail', [
                'content' => \$this->replaceVariables(\$content['body']),
                'variables' => \$this->variables
            ]);
    }
";
    }

    /**
     * Get the stub for a template-based Slack method.
     *
     * @param string $methodName
     * @return string
     */
    protected function getTemplateSlackMethodStub(string $methodName): string
    {
        return "
    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed \$notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function {$methodName}(\$notifiable)
    {
        \$content = \$this->template->getChannelContent('slack');
        \$message = new SlackMessage;
        
        \$message->content(\$this->replaceVariables(\$content['body']));
        
        if (isset(\$content['attachment'])) {
            \$message->attachment(function (\$attachment) use (\$content) {
                \$attachment->title(\$this->replaceVariables(\$content['attachment']['title']));
                
                foreach (\$content['attachment']['fields'] as \$field) {
                    \$attachment->field(
                        \$this->replaceVariables(\$field['title']),
                        \$this->replaceVariables(\$field['value']),
                        \$field['short']
                    );
                }
            });
        }
        
        return \$message;
    }
";
    }

    /**
     * Get the stub for a template-based SMS method.
     *
     * @param string $methodName
     * @return string
     */
    protected function getTemplateSmsMethodStub(string $methodName): string
    {
        return "
    /**
     * Get the SMS representation of the notification.
     *
     * @param mixed \$notifiable
     * @return string
     */
    public function {$methodName}(\$notifiable)
    {
        \$content = \$this->template->getChannelContent('sms');
        return \$this->replaceVariables(\$content['body']);
    }
";
    }

    /**
     * Get the stub for a template-based default method.
     *
     * @param string $methodName
     * @param string $channel
     * @return string
     */
    protected function getTemplateDefaultMethodStub(string $methodName, string $channel): string
    {
        return "
    /**
     * Get the {$channel} representation of the notification.
     *
     * @param mixed \$notifiable
     * @return array
     */
    public function {$methodName}(\$notifiable)
    {
        \$content = \$this->template->getChannelContent('{$channel}');
        return ['body' => \$this->replaceVariables(\$content['body'])];
    }
";
    }

    /**
     * Replace variables in content.
     *
     * @param string $content
     * @return string
     */
    protected function replaceVariables(string $content): string
    {
        return preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            function ($matches) {
                return $this->variables[$matches[1]] ?? $matches[0];
            },
            $content
        );
    }

    /**
     * Get the stub for a template-based mail method.
     *
     * @param string $methodName
     * @param string $templateId
     * @return string
     */
    protected function getTemplateMailMethodStub(string $methodName, string $templateId): string
    {
        return "
    /**
     * Get the mail representation of the notification.
     *
     * @param mixed \$notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function {$methodName}(\$notifiable)
    {
        return (new MailMessage)
            ->view(
                \$this->template['view'],
                \$this->variables
            )
            ->subject(\$this->template['subject'] ?? 'Notification');
    }
";
    }

    /**
     * Generate template properties.
     *
     * @param string $templateId
     * @return string
     */
    protected function generateTemplateProperties(string $templateId): string
    {
        return "
    /**
     * The template identifier.
     *
     * @var string
     */
    protected \$templateId = '{$templateId}';

    /**
     * The template configuration.
     *
     * @var array
     */
    protected \$template;

    /**
     * The template variables.
     *
     * @var array
     */
    protected \$variables = [];

    /**
     * Create a new notification instance.
     *
     * @param array \$variables
     */
    public function __construct(array \$variables = [])
    {
        \$this->template = app(TemplateManager::class)->getTemplate(\$this->templateId);
        \$this->variables = \$variables;
    }
";
    }
}
