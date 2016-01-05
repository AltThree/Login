# Alt Three Login

A GitHub login provider for Laravel 5.


## Installation

Either [PHP](https://php.net) 5.5+ or [HHVM](http://hhvm.com) 3.6+ are required.

To get the latest version of Alt Three Login, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require alt-three/login
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "alt-three/login": "^1.0"
    }
}
```

Once Alt Three Login is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

* `'AltThree\Login\LoginServiceProvider'`


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
