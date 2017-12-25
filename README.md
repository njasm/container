[![Build Status](https://travis-ci.org/njasm/container.svg?branch=master)](https://travis-ci.org/njasm/container) [![Code Coverage](https://scrutinizer-ci.com/g/njasm/container/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/njasm/container/?branch=master) [![Code Climate](https://codeclimate.com/github/njasm/container.png)](https://codeclimate.com/github/njasm/container) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/njasm/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/njasm/container/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/njasm/container/v/stable.png)](https://packagist.org/packages/njasm/container) [![License](https://poser.pugx.org/njasm/container/license.png)](https://packagist.org/packages/njasm/container) 
[![HHVM Status](http://hhvm.h4cc.de/badge/njasm/container.png)](http://hhvm.h4cc.de/package/njasm/container)

## Dependency Container / Service Locator

### Breaking Changes notice.
Branch 2.x.x is a Breaking Changes version in regard to version 1.x.x

### Features
 
 - Common Container Interface (PHP FIG PSR-11) Compliance
 - Alias Service Keys support
 - Circular Dependency guard
 - Primitive data-types registration
 - Automatic Constructor Dependency Resolution and Injection for non-registered Services
 - Lazy and Eager instantiation approaches
 - Lazy and Eager Instantiation Singleton services registration
 - Support for public Setter injection/Method calls after service instantiation
 - Support for public Properties/Attributes Injection after Service instantiation
 - Ability to override existing dependency (Properties & Setters) declarations by supplying new ones when call to ``Container::get``
 - Nested Containers support - They must implement ``\Psr\Container\ContainerInterface``
### Requirements

 - PHP 7.0 or higher.

### Installation

Include ``njasm\container`` in your project, by adding it to your ``composer.json`` file.

```javascript
{
    "require": {
        "njasm/container": "~2.0"
    }
}
```
## Usage

To create a container, simply instantiate the ``Container`` class.

```php
use Njasm\Container\Container;

$container = new Container();
```

### Using Alias

There are time that your ``key`` are too long to be convenient for your client code, one example for instance,
is when binding an ``interface`` to an ``implementation`` or when using for your ``key`` the FQCN of your classes.

```php
namespace Very\Long\Name\Space;

interface SomeInterface {}

class SomeImplementation implements SomeInterface
{
    // code here
}

$container = new Njasm\Container\Container();
$container->set('Very\Long\Name\Space\SomeInterface', new SomeImplementation());
$container->alias('Some', 'Very\Long\Name\Space\SomeInterface');

$some = $container->get('Some');
```

### Defining Services

Services are defined with two params. A ``key`` and a ``value``.
The order you define your services is irrelevant.

#### Defining Services - Primitive data-types

```php
$container->set("Username", "John");

echo $container->get("Username");
```

#### Defining Services - Binding Services (Lazy Loading)

You can bind a ``key`` to a instantiable FCQN ``value``.

 ```php
 $container->bind("MyKey", SomeClass::class);
 ```
 
If you want to bind a Service, and register that Service as a ``Singleton`` Service.
 ```php
 $container->bindSingleton("MyKey", SomeClass::class);
 ```
 
Both ``Container::bind`` and ``Container::bindSingleton`` uses Lazy Loading approach, 
so that ``\My\Namespace\SomeClass`` will only be evaluated/instantiated when ``MyKey`` is requested.

When binding a service, constructor dependencies can be declared, public attributes be set and methods called with
arguments, so they are injected/setted when instantiating the service.

```php
namespace \App\Actors;

class Person {
    protected $name;
    protected $age = 24;
    public genre = 'Male';
    
    public function __construct($name = 'John') {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function setAge($age) {
        $this->age = (int) $age;
    }
}

$container->bind(
    "Person",                      // key
    Person::class,                 // FQCN
    ["Jane"],                      // constructor dependencies
    ["genre" => "Female"],         // attributes injection
    ["setAge" => [33]]             // call methods
);

// binding with chaining methods 
$container->bind("Person", Person::class)
    ->setConstructorArguments(["Jane"])    // setConstructorArgument($index, $argument)
    ->setProperty("genre", "Female")       // setProperties(["genre" => "Female", ...] also work
    ->callMethod("setAge", [33]);          // callMethods(['methodName' => 'methodValue', ...]);
    
// retrieving the object    
$person = $container->get("Person");
echo $person->getName(); // Jane
echo $person->getAge();  // 33
echo $person->genre      // Female

// calling services and overriding declared dependencies 
$person2 = $container->get(
    "Person", 
    ["Mark"], 
    ["genre" => "Male"], 
    ["setAge" => [55]]
);
echo $person2->getName(); // Mark
echo $person2->getAge();  // 55
echo $person2->genre      // Male
```

#### Defining Services - Objects (Eager Loading)

```php
$mailer = new \Namespace\For\My\MailTransport(
    "smtp.example.com", 
    "username", 
    "password", 
    25
); 

$container->set(
    "Mail.Transport", 
    $mailer, 
    [], // constructor args 
    [], // public properties injection
    ["withSSL" => [false]] // calling methods
);

$mailerTransport = $container->get("Mail.Transport");
```
Overwriting existent declared dependencies is also possible for ``set`` definitions.

```php
// calling methods and injecting attributes is also possible
$mailerTransportSsl = $container->get(
    "Mail.Transport", [], [], ["withSSL" => [true]]
);
```

#### Defining Services - Complex builds (Lazy Loading)

There are time when you'll want to instantiate an object, but the build process is somewaht complex and you want to
control that process. You can use an anonymous function for that.

```php
$container->set(
    "Complex",
    function($firstName = "John", $lastName = "Doe") {
        // complex logic here
        // ...
        $theComplexObject = new Complex($firstName, $lastName);
        
        return $theComplexObject;
    }
);

$complex = $container->get("Complex");

// injecting closure dependencies is also possible
$complexJane = $container->get("Complex", ["Jane", "Fonda"]); 
```

#### Defining Services - Complex builds With Nested Dependencies (Lazy Loading)
Creation of nested dependencies is also possible. You just need to pass the container to the closure.

```php
$container->set(
    "Mail.Transport",
    function() use (&$container) {
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

// My\Database is instantiated and is kept a reference to it, 
// so future requests for this service, return this object. 
$db = $container->get("Database.Connection");
$db2 = $container->get("Database.Connection");

// $db === $db2 TRUE
```

### Defining Sub/Nested Containers

Nesting container is possible as long as your container implements ``Psr\Container\ContainerInterface``.
Multiple providers can be registered, and keys are queried on those providers by order 
of registration. Once a key is found it's value is returned.
 
```php
$myAppPsrContainer = new OtherPsrContainer();
$myAppPsrContainer->set("Name", 'John');

$mainContainer = new \Njasm\Container\Container();
$mainContainer->provider($myAppPsrContainer);
 
$mainContainer->has("Name"); // TRUE
echo $mainContainer->get("Name"); // John
```

### Automatic Resolution of Services

When the Container is requested for a service that is not registered, it will try to find the class, and will 
automatically try to resolve your class's constructor dependencies.

```php
namespace My\Name\Space;

class Something
{
    // code
}

// without registering the Something class in the container you can...
$container = new Njasm\Container\Container();
$something = $container->get(Something::class);

//$something instanceof 'My\Name\Space\Something' == true

//once again you can also inject dependencies when calling get method.
$something = $container->get(
    "My\Name\Space\Something", 
    ["constructor value 1", "constructor value 2"],
    ["attributeName" => "value 1"], // attributes
    ["methodName" => ["value 1", "value 2"]]
);
```

### Contributing

Do you wanna help on feature development/improving existing code through refactoring, etc?
Or wanna discuss a feature/bug/idea?
Issues and Pull Requests are welcome as long as you follow some guidelines for PRs:

Pull Requests must:
 - Be PSR-2 compliant.
 - Submit tests with your pull request to your own changes / new code introduction.
 - having fun.
