# Alt Three Login

A GitHub login provider for Laravel 5.


## Installation

This version requires [PHP](https://php.net) 7, and supports Laravel 5.1, 5.2, 5.3, 5.4, or 5.5.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require alt-three/login
```

Once installed, you need to register the `AltThree\Login\LoginServiceProvider` service provider in your `config/app.php`.


## Configuration

Alt Three Login requires configuration.

To get started, you'll need to publish all vendor assets:

```bash
$ php artisan vendor:publish
```

This will create a `config/login.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package between releases.


## Security

If you discover a security vulnerability within this package, please e-mail us at support@alt-three.com. All security vulnerabilities will be promptly addressed.


## License

Alt Three Storage is licensed under [The MIT License (MIT)](LICENSE).
