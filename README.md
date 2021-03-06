# MakePackage

[![Total Downloads](https://poser.pugx.org/ollywarren/makepackage/downloads)](https://packagist.org/packages/ollywarren/makepackage)
[![Latest Stable Version](https://poser.pugx.org/ollywarren/makepackage/v/stable)](https://packagist.org/packages/ollywarren/makepackage)
[![License](https://poser.pugx.org/ollywarren/makepackage/license)](https://packagist.org/packages/ollywarren/makepackage)

### Introduction
Implements a Artisan command to scaffold out Laravel Composer packages

### Installation Laravel 5.5 LTS

1.  Composer require the package:

    ```composer require ollywarren/makepackage```

Let Laravel 5.5 Automatic Package Discovery do its thang! 

### Installation Laravel 5.4

1.  Composer require the package:

    ```composer require ollywarren/makepackage```
    
2.  Register the service provider in ```App\config\app.php``` providers array:

    ```Ollywarren\Makepackage\MakepackageServiceProvider::class```

3.  Navigate to the project root and run ```php artisan``` to check that the ```make:package``` command is present.


### Usage Instructions

1. Simply run ```php artisan make:package``` then follow the on screen instructions.


Enjoy and Make Something Awesome!

### Mentions

Thanks to Sebastiaan Luca <https://github.com/sebastiaanluca/php-stub-generator> for sharing his Stub Generator code now included in this package. Made this package much smaller and much easier to maintain and upgrade in the future.
