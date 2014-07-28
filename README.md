[![Build Status](https://travis-ci.org/njasm/container.svg?branch=master)](https://travis-ci.org/njasm/container) [![Coverage Status](https://coveralls.io/repos/njasm/container/badge.png?branch=master)](https://coveralls.io/r/njasm/container?branch=master) [![Code Climate](https://codeclimate.com/github/njasm/container.png)](https://codeclimate.com/github/njasm/container) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/njasm/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/njasm/container/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/njasm/container/v/stable.png)](https://packagist.org/packages/njasm/container) [![License](https://poser.pugx.org/njasm/container/license.png)](https://packagist.org/packages/njasm/container) 
[![HHVM Status](http://hhvm.h4cc.de/badge/njasm/container.png)](http://hhvm.h4cc.de/package/njasm/container)

## A Service Locator / Dependency Container for PHP

OUTDATED EXAMPLES - More up-to-date/detailed documentation soon.

### Features

 - Primitive Parameters Registration 
 - Automatic Dependency Resolution and Injection for non-registered Services
    - constructor injection only at the moment.
 - Lazy instantiation approach
 - Singleton services available
 - Nested Providers/Containers 

### Requirements

 - PHP 5.3 or higher.

### Installation

Include ``container`` in your project, by adding it to your ``composer.json`` file.

```javascript
{
    "require": {
        "njasm/container": "~1.0"
    }
}
```
## Usage

To create a container, simply instantiate the ``Container`` class.

```php
use Njasm\Container\Container;

$container = new Container();
```

### Defining Services

Services are defined with two params. A ``key`` and a ``value``.

The order you define your services is irrelevant. All services will be instantiated only when requested.
```php
$container->set(
    "Mail.Transport",
    function() {
        return \Namespace\For\My\MailTransport("smtp.example.com", "username", "password", 25);
    }
);
```
Creation of nested dependencies is also possible. You just need to pass the container to the closure.
```php
$container->set(
    "Mail.Transport",
    function() use (&$container) {
        return \Namespace\For\My\MailTransport($container->get("Mail.Transport.Config"));
    }
);

$container->set(
    "Mail.Transport.Config",
    function() {
        return \Namespace\For\My\MailTransportConfig("smtp.example.com", "username", "password", 25);
    }
);

$mailer = $container->get("Mail.Transport");
```

### Defining Singleton Services

For singleton services, you use the singleton method invocation. 
The service will be instantiated the first time when it is requested, if declared as an anonymous function.
You can also register an already instantiated class.

```php
$container->singleton(
    "Database.Connection",
    function() {
        return \Namespace\For\My\Database(
            "mysql:host=example.com;port=3306;dbname=your_db", "username", "password"
        );
    }
);

// MyDatabase is instantiated and stored, for future requests to this service, 
// and then returned.
$db = $container->get("Database.Connection");
$db2 = $container->get("Database.Connection");

// $db === $db2 TRUE

```
### Defining Sub/Nested Containers

 TODO: write example on how to inject other containers, create example also on how to create a Decorator to implement
 the required interface and wrap the wanted container around.

### Roadmap

 - [x] Different storage strategies
 - [x] Allow primitive data types registration
 - [ ] Comply with ``Cointainer-interop`` interfaces

### Contributing

Do you wanna help on feature development/improving existing code through refactoring, etc?
Pull requests are welcome as long as you follow some guidelines:

 - PSR-2 compliant.
 - Submit tests with your pull request to your own changes / new code introduction.
 - having fun.

