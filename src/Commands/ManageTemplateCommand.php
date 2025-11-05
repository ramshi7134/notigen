<?php

namespace Notigen\Commands;

use Illuminate\Console\Command;
use Notigen\TemplateManager;
use Illuminate\Support\Str;

class ManageTemplateCommand extends Command
{
    protected $signature = 'notigen:template
                          {action : Action to perform (create/list/update/delete)}
                          {--name= : Template name}
                          {--channels=* : Notification channels}
                          {--variables=* : Template variables}
                          {--view= : View template path}
                          {--description= : Template description}
                          {--identifier= : Template identifier (for update/delete)}';

    protected $description = 'Manage notification templates';

    protected TemplateManager $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        parent::__construct();
        $this->templateManager = $templateManager;
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'create':
                return $this->createTemplate();
            case 'list':
                return $this->listTemplates();
            case 'update':
                return $this->updateTemplate();
            case 'delete':
                return $this->deleteTemplate();
            default:
                $this->error("Invalid action: {$action}");
                return 1;
        }
    }

    protected function createTemplate()
    {
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Enter template name');
        }

        $channels = $this->getChannels();
        $variables = $this->getVariables();
        $view = $this->option('view');
        $description = $this->option('description');

        try {
            $template = $this->templateManager->createTemplate(
                $name,
                $channels,
                $variables,
                $view,
                $description
            );

            $this->info("Template created successfully!");
            $this->info("Template Identifier: " . $template->identifier);
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    protected function listTemplates()
    {
        $templates = $this->templateManager->getTemplates();
        
        $headers = ['Identifier', 'Name', 'Channels', 'Variables'];
        $rows = [];

        foreach ($templates as $template) {
            $rows[] = [
                $template->identifier,
                $template->name,
                implode(', ', $template->channels),
                implode(', ', array_keys($template->variables ?? [])),
            ];
        }

        $this->table($headers, $rows);
        return 0;
    }

    protected function updateTemplate()
    {
        $identifier = $this->option('identifier');
        if (!$identifier) {
            $identifier = $this->ask('Enter template identifier');
        }

        $channels = $this->getChannels();

        try {
            $template = $this->templateManager->updateTemplateChannels($identifier, $channels);
            
            $this->info("Template updated successfully!");
            $this->info("Active channels: " . implode(', ', $template->channels));
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    protected function deleteTemplate()
    {
        $identifier = $this->option('identifier');
        if (!$identifier) {
            $identifier = $this->ask('Enter template identifier');
        }

        try {
            $template = $this->templateManager->getTemplate($identifier);
            if (!$template) {
                throw new \InvalidArgumentException("Template not found: {$identifier}");
            }

            $template->update(['is_active' => false]);
            
            $this->info("Template deactivated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    protected function getChannels(): array
    {
        $channels = $this->option('channels');
        
        if (empty($channels)) {
            $availableChannels = $this->templateManager->getAvailableChannels();
            $choices = array_map(function ($channel, $key) {
                return "{$key} ({$channel['name']})";
            }, $availableChannels, array_keys($availableChannels));

            $selected = $this->choice(
                'Select channels (comma-separated)',
                $choices,
                0,
                null,
                true
            );

            $channels = array_map(function ($choice) {
                return Str::before($choice, ' (');
            }, $selected);
        }

        return $channels;
    }

    protected function getVariables(): array
    {
        $variables = [];
        $rawVariables = $this->option('variables');

        if (empty($rawVariables)) {
            if ($this->confirm('Do you want to add variables?', true)) {
                do {
                    $name = $this->ask('Variable name');
                    $description = $this->ask('Variable description');
                    $variables[$name] = $description;
                } while ($this->confirm('Add another variable?', false));
            }
        } else {
            foreach ($rawVariables as $variable) {
                [$name, $description] = array_pad(explode(':', $variable), 2, '');
                $variables[$name] = $description ?: $name;
            }
        }

        return $variables;
    }
}
