# JWT Auth Guard

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

> JWT Auth Guard is a Laravel & Lumen Package that lets you use `jwt` as your driver for authentication guard in your application.
> 
> The Guard uses `tymon/jwt-auth` package for authentication and token handling.

## Requirements
- Laravel or Lumen Installation.
- [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) `^1.0@dev` Package Setup and Config'd.

## Pre-Installation

First install and setup [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) package.

``` bash
$ composer require tymon/jwt-auth:^1.0@dev
```

Once done, config it and then install this package.

## Install

Via Composer

``` bash
$ composer require irazasyed/jwt-auth-guard
```

### Add the Service Provider

#### Laravel

Open `config/app.php` and, to your `providers` array at the bottom, add:

```php
Irazasyed\JwtAuthGuard\JwtAuthGuardServiceProvider::class
```

#### Lumen

Open `bootstrap/app.php` and register the service provider:

``` php
$app->register(Irazasyed\JwtAuthGuard\JwtAuthGuardServiceProvider::class);
```

## Usage

Open your `config/auth.php` config file and in place of driver under any of your guards, just add the `jwt-auth` as your driver and you're all set.
Make sure you also set `provider` for the guard to communicate with your database.

### Setup Guard Driver

``` php
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'jwt-auth',
        'provider' => 'users'
    ],
    
    // ...
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\User::class,
    ],
],
```

### Middleware Usage

Middleware protecting the route:

``` php
Route::get('api/content', ['middleware' => 'auth:api', 'uses' => 'ContentController@content']);
```

Middleware protecting the controller:

``` php
<?php

namespace App\Http\Controllers;

class ContentController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth:api');
    }
}
```

**Note:** The above example assumes you've setup a guard with the name `api` whose driver is `jwt-auth` in your `config/auth.php` file as explained in "Setup Guard Driver" section above.

> The following usage examples assume you've setup your default auth guard to the one which uses the `jwt-auth` driver.
>
> You can also explicitly define the guard before making calls to any of methods by just prefixing it with `Auth::guard('api')`. 
>
> Example: `Auth::guard('api')->user()`

### Attempt To Authenticate And Return Token

``` php
// This will attempt to authenticate the user using the credentials passed and returns a JWT Auth Token for subsequent requests.
$token = Auth::attempt(['email' => 'user@domain.com', 'password' => '123456']);
```

### Authenticate Once By ID

``` php
if(Auth::onceUsingId(1)) {
    // Do something with the authenticated user
}
```

### Authenticate Once By Credentials

``` php
if(Auth::once(['email' => 'user@domain.com', 'password' => '123456'])) {
    // Do something with the authenticated user
}
```

### Validate Credentials

``` php
if(Auth::validate(['email' => 'user@domain.com', 'password' => '123456'])) {
    // Credentials are valid
}
```

### Check User is Authenticated

``` php
if(Auth::check()) {
    // User is authenticated
}
```

### Check User is a Guest

``` php
if(Auth::guest()) {
    // Welcome guests!
}
```

### Logout Authenticated User

``` php
Auth::logout(); // This will invalidate the current token and unset user/token values.
```

### Generate JWT Auth Token By ID
   
``` php
$token = Auth::generateTokenById(1);

echo $token;
```

### Get Authenticated User

Once the user is authenticated via a middleware, You can access its details by doing:

``` php
$user = Auth::user();
```

You can also manually access user info using the token itself:

``` php
$user = Auth::setToken('YourJWTAuthToken')->user();
```

### Get Authenticated User's ID

``` php
$userId = Auth::id();
```

### Refresh Expired Token

Though it's recommended you refresh using the middlewares provided with the package,
but if you'd like, You can also do it manually with this method.

Refresh expired token passed in request:

``` php
$token = Auth::refresh();
```

Refresh passed expired token:

``` php
Auth::setToken('ExpiredToken')->refresh();
```

### Invalidate Token

Invalidate token passed in request:

``` php
$forceForever = false;
Auth::invalidate($forceForever);
```

Invalidate token by setting one manually:

``` php
$forceForever = false;
Auth::setToken('TokenToInvalidate')->invalidate($forceForever);
```

### Get Token

``` php
$token = Auth::getToken(); // Returns current token passed in request.
```

### Get Token Payload

This method will decode the token and return its raw payload.

Get Payload for the token passed in request:

``` php
$payload = Auth::getPayload();
```

Get Payload for the given token manually:

``` php
$payload = Auth::setToken('TokenToGetPayload')->getPayload();
```

## Change log

Please see [CHANGELOG](.github/CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CONDUCT.md) for details.

Any issues, feedback, suggestions or questions please use issue tracker [here](https://github.com/irazasyed/jwt-auth-guard/issues).

## Security

If you discover any security related issues, please email syed+gh@lukonet.com instead of using the issue tracker.

## Credits

- [Syed Irfaq R.][link-author]
- [All Contributors][link-contributors]


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/irazasyed/jwt-auth-guard.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/irazasyed/jwt-auth-guard/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/irazasyed/jwt-auth-guard.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/irazasyed/jwt-auth-guard.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/irazasyed/jwt-auth-guard.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/irazasyed/jwt-auth-guard
[link-travis]: https://travis-ci.org/irazasyed/jwt-auth-guard
[link-scrutinizer]: https://scrutinizer-ci.com/g/irazasyed/jwt-auth-guard/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/irazasyed/jwt-auth-guard
[link-downloads]: https://packagist.org/packages/irazasyed/jwt-auth-guard
[link-author]: https://github.com/irazasyed
[link-contributors]: ../../contributors
