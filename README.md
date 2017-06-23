#MakePackage
##Artisan Command Package to Scaffold Composer Package Projects

###Introduction
Implements a Artisan command to scaffold out Laravel Composer packages

###Installation

1.  Composer require the package:

    ```composer require ollywarren/makepackage```
    
2.  Register the service provider in ```App\config\app.php``` providers array:

    ```Ollywarren\Makepackage\MakepackageServiceProvider::class```

3.  Navigate to the project root and run ```php artisan``` to check that the ```make:package``` command is present.


###Usage Instructions

###License
