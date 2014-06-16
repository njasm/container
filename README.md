[![Build Status](https://travis-ci.org/njasm/services-container.svg?branch=master)](https://travis-ci.org/njasm/services-container) [![Coverage Status](https://coveralls.io/repos/njasm/services-container/badge.png?branch=master)](https://coveralls.io/r/njasm/services-container?branch=master) [![Code Climate](https://codeclimate.com/github/njasm/services-container.png)](https://codeclimate.com/github/njasm/services-container) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/njasm/services-container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/njasm/services-container/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/njasm/services-container/v/stable.png)](https://packagist.org/packages/njasm/services-container) [![License](https://poser.pugx.org/njasm/services-container/license.png)](https://packagist.org/packages/njasm/services-container) 
[![HHVM Status](http://hhvm.h4cc.de/badge/njasm/services-container.png)](http://hhvm.h4cc.de/package/njasm/services-container)

## A Services/Dependency Container for PHP

More detailed documentation soon.

### Features

 - Lazy instantiation approach
 - Singleton services available
 - Nested Providers/Containers 

### Requirements

 - PHP 5.3 or higher.

### Installation

Include ``Services-Container`` in your project, by adding it to your ``composer.json`` file.

```javascript
{
    "require": {
        "njasm/services-container": "~1.0"
    }
}
```
## Usage

To create a container, simply instantiate the ``ServicesContainer`` class.

```php
use Njasm\ServicesContainer\ServicesContainer;

$container = new ServicesContainer();
```

### Defining Services

The order you define your services is irrelevant. All services will be instantiated only when requested.
```php
$container->set(
    "MAIL.TRANSPORT",
    function() {
        return \Namespace\For\My\MailTransport("smtp.example.com", "username", "password", 25);
    }
);
```
Creation of nested dependencies is also possible. You just need to pass the container to the closure.
```php
$container->set(
    "MAIL.TRANSPORT",
    function() use (&$container) {
        return \Namespace\For\My\MailTransport($container->get("MAIL.TRANSPORT.CONFIG"));
    }
);

$container->set(
    "MAIL.TRANSPORT.CONFIG",
    function() {
        return \Namespace\For\My\MailTransportConfig("smtp.example.com", "username", "password", 25);
    }
);

$mailer = $container->get("MAIL.TRANSPORT");
```

### Defining Singleton Services

For singleton services, you use the singleton method invocation. the service will be instantiated the first time
it is requested, all future requests for that service, will return the same object.

```php
$container->singleton(
    "DATABASE.CONNECTION",
    function() {
        return \Namespace\For\My\Database(
            "mysql:host=example.com;port=3306;dbname=your_db", "username", "password"
        );
    }
);

// MyDatabase is instantiated and stored, for future requests to this service, 
// and then returned.
$db = $container->get("DATABASE.CONNECTION");

// now the stored instance of MyDatabase is returned.
$db2 = $container->get("DATABASE.CONNECTION");

```
### Defining Sub/Nested Containers

 TODO: write example on how to inject other containers, create example also on how to create a Decorator to implement
 the required interface and wrap the wanted container around.

### Roadmap

 - Different storage strategies
 - allow primitive data types registration
 - Comply with Cointainer-interop interfaces

### Contributing

Do you wanna help on feature development/improve existing code through refactoring, etc?
Pull requests are welcome as long as you follow some guidelines:

 - PSR2 compliant.
 - Submit tests with your pull request to your own changes / new code introduction.
 - having fun.

