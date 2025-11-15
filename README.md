# Notigen

A Laravel package for managing notification templates with variable support and multiple channel capabilities. Create, manage, and send notifications with a beautiful web interface.

## Installation

You can install the package via composer:

```bash
composer require webpacks/notigen
```

## Setup

1. Publish the assets and config (package service provider is auto-discovered, but you can publish assets):

```bash
php artisan vendor:publish --provider="Notigen\NotigenServiceProvider" --tag=notigen-assets
```

2. Run migrations:

```bash
php artisan migrate
```

3. (Optional) If your application does not auto-discover packages, add the service provider and facade to `config/app.php`:

```php
'providers' => [
    // ...
    Notigen\NotigenServiceProvider::class,
],

'aliases' => [
    // ...
    'Notigen' => Notigen\Facades\Notigen::class,
]
```

## Usage

### 1. Web Interface

Access the web interface at `/notigen` to manage your notification templates:

- Create new templates with variables
- Preview templates with test data
- Manage existing templates
- Configure notification channels

### 2. Programmatic Usage

#### Create a Template:

```php
use Notigen\Facades\Notigen;

$template = Notigen::createTemplate([
    'name' => 'Welcome Email',
    'template_key' => 'welcome_email', // Optional, will be auto-generated
    'content' => 'Hello {user_name}, Welcome to {app_name}!',
    'variables' => [
        ['name' => 'user_name', 'description' => "User's full name"],
        ['name' => 'app_name', 'description' => 'Application name']
    ],
    'channels' => ['mail', 'database'], // Optional, defaults to ['mail']
    'subject' => 'Welcome to {app_name}', // Optional, for email channel
    'description' => 'Template for welcome emails' // Optional
]);
```

#### Send Notifications:

```php
// Using template key
Notigen::send('welcome_email', [
    'user_name' => 'John Doe',
    'app_name' => 'My App'
], $user); // $user is the notifiable entity

// Or using template object
$template = Notigen::getTemplate('welcome_email');
$template->send([
    'user_name' => 'John Doe',
    'app_name' => 'My App'
], $user);
```

#### Preview Content:

```php
// Preview with variables replaced
$content = Notigen::preview('welcome_email', [
    'user_name' => 'John Doe',
    'app_name' => 'My App'
]);
```

#### Template Management:

```php
// Get all templates
$templates = Notigen::getAllTemplates();

// Get a specific template
$template = Notigen::getTemplate('welcome_email');

// Update a template
Notigen::updateTemplate('welcome_email', [
    'content' => 'New content with {user_name}',
    'variables' => [
        ['name' => 'user_name', 'description' => 'Updated description']
    ]
]);

// Delete a template
Notigen::deleteTemplate('welcome_email');
```

### Configuration

Configuration options in `config/notigen.php`:

```php
return [
    // Default notification channels
    'default_channels' => ['mail'],

    // Route prefix for the web interface
    'route_prefix' => 'notigen',

    // Middleware for the web interface
    'middleware' => ['web', 'auth'],

    // Database table names
    'tables' => [
        'templates' => 'notification_templates',
        'variables' => 'template_variables'
    ]
];
```

## Features

- ✨ Beautiful web interface for template management
- 📝 Variable support with validation
- 📫 Multiple notification channel support
- 🔑 Automatic template key generation
- 🔒 CSRF protection and validation
- 👀 Live preview functionality
- 🚀 Easy integration with existing Laravel apps
- 🎨 Clean and intuitive UI/UX
- ♻️ Event-driven architecture

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email chatwith@ramsheed.com instead of using the issue tracker.

## Credits

- [Ramsheed](https://github.com/ramshi7134)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
