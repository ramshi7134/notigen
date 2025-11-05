<?php

namespace Notigen;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class TemplateManager
{
    /**
     * Register a new notification template.
     *
     * @param string $name Template name
     * @param array $channels Notification channels
     * @param array $variables Required variables
     * @param string|null $view Custom view name
     * @return string Template identifier
     */
    public function registerTemplate(string $name, array $channels, array $variables, ?string $view = null): string
    {
        $identifier = Str::slug($name) . '_' . Str::random(8);
        
        $template = [
            'name' => $name,
            'description' => '',
            'channels' => $channels,
            'variables' => $variables,
            'view' => $view ?? "notigen::templates.{$identifier}",
        ];

        Config::set("notigen.templates.{$identifier}", $template);
        
        return $identifier;
    }

    /**
     * Get template configuration by identifier.
     *
     * @param string $identifier
     * @return array|null
     */
    public function getTemplate(string $identifier): ?array
    {
        return Config::get("notigen.templates.{$identifier}");
    }

    /**
     * Validate template variables.
     *
     * @param string $identifier
     * @param array $variables
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateVariables(string $identifier, array $variables): bool
    {
        $template = $this->getTemplate($identifier);
        
        if (!$template) {
            throw new \InvalidArgumentException("Template with identifier '{$identifier}' not found.");
        }

        $requiredVariables = array_keys($template['variables']);
        $missingVariables = array_diff($requiredVariables, array_keys($variables));

        if (!empty($missingVariables)) {
            throw new \InvalidArgumentException(
                "Missing required variables for template '{$identifier}': " . implode(', ', $missingVariables)
            );
        }

        return true;
    }
}
