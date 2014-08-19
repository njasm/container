[![Build Status](https://travis-ci.org/njasm/container.svg?branch=master)](https://travis-ci.org/njasm/container) [![Coverage Status](https://coveralls.io/repos/njasm/container/badge.png?branch=master)](https://coveralls.io/r/njasm/container?branch=master) [![Code Climate](https://codeclimate.com/github/njasm/container.png)](https://codeclimate.com/github/njasm/container) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/njasm/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/njasm/container/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/njasm/container/v/stable.png)](https://packagist.org/packages/njasm/container) [![License](https://poser.pugx.org/njasm/container/license.png)](https://packagist.org/packages/njasm/container) 
[![HHVM Status](http://hhvm.h4cc.de/badge/njasm/container.png)](http://hhvm.h4cc.de/package/njasm/container)

## Dependency Container / Service Locator


### Features

 - Primitive data-types registration
 - Automatic Dependency Resolution and Injection for non-registered Services
    - constructor injection only at the moment.
 - Lazy and Eager instantiation approaches
 - Lazy and Eager Instantiation Singleton services registration
 - Nested Providers/Containers 
 - Comply with ``Container-Interop`` interfaces

### Requirements

 - PHP 5.3 or higher.

### Installation

Include ``njasm\container`` in your project, by adding it to your ``composer.json`` file.

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
The order you define your services is irrelevant.

#### Defining Services - Primitive data-types

```php
$container->set("Username", "John");

echo $container->get("Username");
```

#### Defining Services - Eager Loading

```php
$container->set(
    "Mail.Transport",
    new \Namespace\For\My\MailTransport("smtp.example.com", "username", "password", 25)
);

$mailer = $container->get("Mail.Transport");
```

#### Defining Services - Lazy Loading

There are time when you'll want to instantiate an object, only if needed in the current request. You use
anonymous functions for that.

```php
$container->set(
    "Mail.Transport",
    function() {
        return new \Namespace\For\My\MailTransport(
            "smtp.example.com", 
            "username", 
            "password", 
            25
        );
    }
);

$mailer = $container->get("Mail.Transport");
$mailer->setMessage($messageObject)->send();
```

Creation of nested dependencies is also possible. You just need to pass the container to the closure.

```php
$container->set(
    "Mail.Transport",
    function(&$container) {
        return new \Namespace\For\My\MailTransport(
            $container->get("Mail.Transport.Config")
        );
    }
);

$container->set(
    "Mail.Transport.Config",
    function() {
        return new \Namespace\For\My\MailTransportConfig(
            "smtp.example.com", 
            "username", 
            "password", 
            25
        );
    }
);

$mailer = $container->get("Mail.Transport");
```

#### Defining Singleton Services

For registering singleton services, you use the singleton method invocation.
The example below makes it to be a Lazy loading singleton service, cos we're registering it with 
an anonymous function.

```php
$container->singleton(
    "Database.Connection",
    function() {
        return new \Namespace\For\My\Database(
            "mysql:host=example.com;port=3306;dbname=your_db", 
            "username", 
            "password"
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

### Automatic Resolution of Services

When the Container is requested for a service that is not registered, it will try to find the class, and will 
automatically try to resolve your class's constructor dependencies.

Please check the ``ReflectionBuilderTest.php`` under the ``tests/Builder`` folder for detailed examples.

TODO: create example in README.md.

### Roadmap

In no Particular order - check Milestones for a more organized picture.

 - [ ] Load definitions from configuration files
 - [ ] Support for alias keys 
 - [ ] Support for Setter injection
 - [ ] Support for Properties/Attributes Injection
 - [ ] Able to override existing dependency declarations by supplying new ones when call to ``Container::get``
 - [ ] Optimizations

### Contributing

Do you wanna help on feature development/improving existing code through refactoring, etc?
Or wanna discuss a feature/bug/idea?
Issues and Pull Requests are welcome as long as you follow some guidelines for PRs:

Pull Requests must:
 - Be PSR-2 compliant.
 - Submit tests with your pull request to your own changes / new code introduction.
 - having fun.
