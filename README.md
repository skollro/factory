# Higher order factories

[![Latest Version](https://img.shields.io/github/release/skollro/factory.svg?style=flat-square)](https://github.com/skollro/factory/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/skollro/factory/master.svg?style=flat-square)](https://travis-ci.org/skollro/factory)
[![StyleCI](https://styleci.io/repos/127891436/shield)](https://styleci.io/repos/127891436)

This package provides a declarative higher order syntax for implementing the factory pattern.

```php
use function Skollro\Factory\make;

class VehicleFactory
{
    public static function make($type)
    {
        return make($type)
            ->resolve('car', Car::class)
            ->resolve('bike', Bike::class)
            ->resolve([
                'truck' => Truck::class,
                'bus' => Bus::class,
            ])
            ->otherwiseThrow(InvalidArgumentException::class);
    }
}

$car = VehicleFactory::make('car'); // returns instance of Car
$truck = VehicleFactory::make('truck'); // returns instance of Truck
$motorcycle = VehicleFactory::make('motorcycle') // throws InvalidArgumentException
```

## Install

You can install this package via composer:

``` bash
composer require skollro/otherwise
```

## Usage

Every `make` consists out of one or multiple `resolve` and one `otherwise` to provide values for each path.

#### `make($type): Factory`

This package provides a helper function `make`.

```php
use Skollro\Factory\Factory;
use function Skollro\Factory\make;

$factory = make($type);
$factory = Factory::make($type);
```

#### `resolve($type, $resolvable = null): Factory`

`$type` is the identifier of an instance. `$type` also accepts an associative array which maps identifiers to class names. `$resolvable` can be some class name or a callable that returns an instance.

```php
$object = make('bike')
    ->resolve('car', Car::class)
    ->resolve('bike', function () {
        return new Bike;
    })
    ->resolve('bike', MountainBike::class) // this is not the first match and thus not the result
    ->resolve([
        'truck' => Truck::class,
        'bus' => Bus::class,
    ])
    ->otherwiseThrow(InvalidArgumentException::class);

// $object is an instance of Bike
```

#### `otherwise($resolvable)`

`$resolvable` is some class name or a callable that returns an instance. Supplies the default instance if no `resolve` has matched before.

```php
$object = make('motorcycle')
    ->resolve([
        'car' => Car::class,
        'bike' => Bike::class,
    ])
    ->otherwise(Vehicle::class);

// $object is an instance of Vehicle

$object = make('motorcycle')
    ->resolve([
        'car' => Car::class,
        'bike' => Bike::class,
    ])
    ->otherwise(function () {
        return new Vehicle;
    });

// $object is an instance of Vehicle
```

#### `otherwiseThrow($resolvable)`

`$resolvable` is some exception class name or a callable that returns an exception. Throws an exception if no `resolve` has matched before.

```php
$object = make('motorcycle')
    ->resolve([
        'car' => Car::class,
        'bike' => Bike::class,
    ])
    ->otherwiseThrow(InvalidArgumentException::class);

// throws an InvalidArgumentException
```

#### `Factory::resolveUsing($resolver)`

`resolveUsing` is a static method of `Factory`. You can use a custom resolver to resolve an instance from a class name, e.g. a dependency injection container. If no resolver is set, an instance of the requested class is created with `new`.

##### Integration with Laravel
```php
// in your AppServiceProvider
Factory::resolveUsing(function ($className) {
    return app($className);
});

// in your code
$object = make('car')
    ->resolve('car', Car::class)
    ->otherwiseThrow(InvalidArgumentException::class);

// $object is an instance of Car, resolved by Laravel container
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
