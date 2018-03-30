# Alt Three Login

An OAuth 2 login provider for Laravel 5.

We currently support [GitHub](https://github.com/), [GitLab](https://gitlab.com/) and [Bitbucket](https://bitbucket.org/).


## Installation

This version requires [PHP](https://php.net) 7.1 or 7.2, and supports Laravel 5.5 or 5.6.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require alt-three/login
```

Once installed, if you are not using automatic package discovery, then you need to register the `AltThree\Login\LoginServiceProvider` service provider in your `config/app.php`.

Finally, if you want to use the [Bitbucket](https://bitbucket.org/) provider, you'll also need to install [Alt Three UUID](https://github.com/AltThree/Uuid) package:

```bash
$ composer require alt-three/uuid
```


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
