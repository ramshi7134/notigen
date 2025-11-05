<?php

namespace Notigen\Commands;

use Illuminate\Console\Command;
use Notigen\TemplateManager;
use Illuminate\Support\Str;

class EditTemplateContentCommand extends Command
{
    protected $signature = 'notigen:template-content
                          {identifier : Template identifier}
                          {channel : Channel to edit (mail/slack/sms)}
                          {--subject= : Subject for email notifications}
                          {--content= : Content of the notification}
                          {--editor : Open in text editor}';

    protected $description = 'Edit template content for specific channels';

    protected TemplateManager $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    public function handle()
    {
        $identifier = $this->argument('identifier');
        $channel = $this->argument('channel');

        try {
            $template = $this->templateManager->getTemplate($identifier);
            
            if (!$template) {
                throw new \InvalidArgumentException("Template not found: {$identifier}");
            }

            if (!in_array($channel, $template->channels)) {
                throw new \InvalidArgumentException("Channel '{$channel}' is not enabled for this template");
            }

            $content = $this->getContent($channel);
            
            $template->setChannelContent($channel, $content);
            $template->save();

            $this->info("Template content updated successfully for channel: {$channel}");
            return 0;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    protected function getContent(string $channel): array
    {
        $content = [];

        switch ($channel) {
            case 'mail':
                $content = $this->getMailContent();
                break;
            case 'slack':
                $content = $this->getSlackContent();
                break;
            case 'sms':
                $content = $this->getSmsContent();
                break;
            default:
                $content['body'] = $this->getGenericContent();
        }

        return $content;
    }

    protected function getMailContent(): array
    {
        $content = [];
        
        if ($this->option('editor')) {
            $content['subject'] = $this->option('subject') ?? $this->ask('Enter email subject');
            $content['body'] = $this->openInEditor('Enter email content (Markdown supported)');
        } else {
            $content['subject'] = $this->option('subject') ?? $this->ask('Enter email subject');
            $content['body'] = $this->option('content') ?? $this->anticipate('Enter email content (Markdown supported)', []);
        }

        // Preview variables
        $this->previewVariables($content['body']);

        return $content;
    }

    protected function getSlackContent(): array
    {
        $content = [];
        
        if ($this->option('editor')) {
            $content['body'] = $this->openInEditor('Enter Slack message content');
        } else {
            $content['body'] = $this->option('content') ?? $this->anticipate('Enter Slack message content', []);
        }

        // Additional Slack-specific options
        if ($this->confirm('Add attachment?', false)) {
            $content['attachment'] = [
                'title' => $this->ask('Attachment title'),
                'fields' => $this->getSlackFields()
            ];
        }

        $this->previewVariables($content['body']);

        return $content;
    }

    protected function getSmsContent(): array
    {
        $content = [];
        
        $content['body'] = $this->option('content') ?? 
                          $this->anticipate('Enter SMS content (Keep it concise)', []);

        $this->previewVariables($content['body']);

        return $content;
    }

    protected function getGenericContent(): string
    {
        return $this->option('content') ?? 
               $this->anticipate('Enter notification content', []);
    }

    protected function getSlackFields(): array
    {
        $fields = [];
        
        while ($this->confirm('Add field?', false)) {
            $fields[] = [
                'title' => $this->ask('Field title'),
                'value' => $this->ask('Field value'),
                'short' => $this->confirm('Short field?', false)
            ];
        }

        return $fields;
    }

    protected function previewVariables(string $content): void
    {
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $content, $matches);
        
        if (!empty($matches[1])) {
            $this->info('Variables found in content:');
            foreach (array_unique($matches[1]) as $variable) {
                $this->line("- {$variable}");
            }
        }
    }
}
