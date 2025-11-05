# Notigen

A Laravel package that simplifies the process of creating, managing, and sending custom notifications.

## Installation

You can install the package via composer:

```bash
composer require notigen/notigen
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="notigen-config"
```

## Usage

### Creating a New Notification

You can create a new notification using the following command:

```bash
php artisan make:notification-custom UserWelcome --channels=mail,database,slack
```

This will create a new notification class with the specified channels.

### Available Options

- `--channels`: Specify the notification channels (mail, database, slack, etc.)

### Configuration Options

You can configure the following options in the `config/notigen.php` file:

- `default_channels`: Default notification channels
- `templates_path`: Custom templates path
- `queue_notifications`: Enable/disable queue by default
- `default_queue`: Default queue name for notifications

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email your.email@example.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
