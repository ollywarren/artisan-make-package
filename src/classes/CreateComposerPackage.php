<?php

namespace Ollywarren\Makepackage\Classes;

use Illuminate\Console\Command;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class CreateComposerPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a skeleton Laravel Composer Package';


    /**
     * FlySystem Configuration.
     */
    protected $adaptor;
    protected $filesystem;


    /**
     * CreateComposerPackage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->adaptor      = new Local(base_path(), 1, Local::SKIP_LINKS, []);
        $this->filesystem   = new Filesystem($this->adaptor);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("
              _                ___           _                           
  /\/\   __ _| | _____   _    / _ \__ _  ___| | ____ _  __ _  ___        
 /    \ / _` | |/ / _ \ (_)  / /_)/ _` |/ __| |/ / _` |/ _` |/ _ \       
/ /\/\ \ (_| |   <  __/  _  / ___/ (_| | (__|   < (_| | (_| |  __/       
\/    \/\__,_|_|\_\___| (_) \/    \__,_|\___|_|\_\__,_|\__, |\___|       
                                                       |___/             
   _        _   _                   __            __  __       _     _   
  /_\  _ __| |_(_)___  __ _ _ __   / _\ ___ __ _ / _|/ _| ___ | | __| |  
 //_\\| '__| __| / __|/ _` | '_ \  \ \ / __/ _` | |_| |_ / _ \| |/ _` |  
/  _  \ |  | |_| \__ \ (_| | | | | _\ \ (_| (_| |  _|  _| (_) | | (_| |  
\_/ \_/_|   \__|_|___/\__,_|_| |_| \__/\___\__,_|_| |_|  \___/|_|\__,_|  
                                                                         
                                                                         
 _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ 
|_____|_____|_____|_____|_____|_____|_____|_____|_____|_____|_____|_____|
                                                                                                      
            ");

        //Define the Required Input for the Command
        $vendor_name            = strtolower($this->ask('What Vendor Name do you wish to use?'));
        $package_name           = strtolower($this->ask('What is the name of the package you are creating?'));
        $package_description    = $this->ask('Please describe what your package will do');

        //Lets create the directory structure we need within Laravel.
        $structure       = $this->createBaseStructure($vendor_name, $package_name, $package_description);


        //Lets create the Composer.json File fro the Project. This also stops it being overwritten by this command.
        $author_name        = $this->ask('What is the Author name?');
        $author_email       = $this->ask('What is the Author email?');
        $author             = ['name' => $author_name, 'email' => $author_email];

        $composer_json   = $this->createComposerJson($vendor_name, $package_name, $package_description, $author);

        //Service Provider
        $service_provider = $this->choice('Do you need a Laravel Service Provider for this Package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($service_provider != 'No') {
                $service_provider_file = $this->createServiceProvider($vendor_name, $package_name);
        }

        //Facade
        $facade = $this->choice('Do you need a Laravel Facade for this Package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($facade != 'No') {
            //lets create a facade
            $facade_file = $this->createFacade($vendor_name, $package_name);
        }

        //Testing
        $testing = $this->choice('Do you need to unit test this package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($testing != 'No') {
            $testing = $this->createUnitTesting($vendor_name, $package_name);
        }

        $this->info("
   ___                                 ___                      _      _           _                                         
  / _ \_ __ ___   ___ ___  ___ ___    / __\___  _ __ ___  _ __ | | ___| |_ ___  __| |                                        
 / /_)/ '__/ _ \ / __/ _ \/ __/ __|  / /  / _ \| '_ ` _ \| '_ \| |/ _ \ __/ _ \/ _` |                                        
/ ___/| | | (_) | (_|  __/\__ \__ \ / /__| (_) | | | | | | |_) | |  __/ ||  __/ (_| |                                        
\/    |_|  \___/ \___\___||___/___/ \____/\___/|_| |_| |_| .__/|_|\___|\__\___|\__,_|                                        
                                                         |_|                                                                 
 __ _                      __                      _   _     _                 _                                           _ 
/ _\ |__   __ _ _ __ ___  / _\ ___  _ __ ___   ___| |_| |__ (_)_ __   __ _    /_\__      _____  ___  ___  _ __ ___   ___  / \
\ \| '_ \ / _` | '__/ _ \ \ \ / _ \| '_ ` _ \ / _ \ __| '_ \| | '_ \ / _` |  //_\\ \ /\ / / _ \/ __|/ _ \| '_ ` _ \ / _ \/  /
_\ \ | | | (_| | | |  __/ _\ \ (_) | | | | | |  __/ |_| | | | | | | | (_| | /  _  \ V  V /  __/\__ \ (_) | | | | | |  __/\_/ 
\__/_| |_|\__,_|_|  \___| \__/\___/|_| |_| |_|\___|\__|_| |_|_|_| |_|\__, | \_/ \_/\_/\_/ \___||___/\___/|_| |_| |_|\___\/   
                                                                     |___/                                                   
");
    }

    /**
     * createBaseStructure
     *
     * Creates the base directory structure for the Package project.
     * Assumes the root path will be used to create a `packages/{vendor}/{package}`.
     *
     * Looks for the existence of a composer.json to determine if a package project
     * has already been created.
     *
     * Todo: Figure out a better way to determine if a directory structure exists
     *
     * @param $vendor
     * @param $package
     *
     * @author  Olly Warren
     * @package MakePackage Artisan Command.
     * @version 1.0
     */
    public function createBaseStructure($vendor, $package, $description)
    {
	    //Check if the root composer.json for the package exists.. this will tell us if the structure is already in place.
        $check = $this->filesystem->has("packages/{$vendor}/{$package}/composer.json");

        //package already exists
        if ($check == true) {
            $this->error("I have found a composer.json file in this directory... package already exists");
        }

        $structure = $this->filesystem->createDir("packages/{$vendor}/{$package}/");
        $src = $this->filesystem->createDir("packages/{$vendor}/{$package}/src");

        //Drops a git ignore file in to ignore the vendor directory
        $gitignore = $this->filesystem->put("packages/{$vendor}/{$package}/.gitignore", "/vendor");

        $readme = $this->filesystem->put("packages/{$vendor}/{$package}/README.md", "#{$vendor} - {$package}\n{$description}");

        if ($structure == false) {
                $this->error("There was an error creating the directory structure. Please check your permissions.");
        }
    }

    /**
     * createComposerJson
     *
     * Writes the basic composer.json file to the package project directory.
     *
     *
     * @param $vendor
     * @param $package
     * @param $description
     * @param $author
     *
     * @author  Olly Warren
     * @package MakePackage Artisan Command
     * @version 1.1
     */
    public function createComposerJson($vendor, $package, $description, $author)
    {
        /**
         * @since 1.1
         */
        //Construct a PHP object to translate into JSON
        $template = new \stdClass();

        $template->name             = $vendor.'/'.$package;
        $template->description      = $description;

        $authorObj = new \stdClass();
        $authorObj->name = $author['name'];
        $authorObj->email = $author['email'];
        $template->authors          = array($authorObj);

        $template->{"require-dev"}  = new \stdClass();
        $template->require          = new \stdClass();
        $template->autoload         = [
            'psr-4' =>  [
                $vendor.'\\'.$package.'\\' => 'src/'
            ]
        ];
        $template->extra            = [
            'branch-alias' => new \stdClass()
        ];
        $template->{"minimum-stability"}  = 'dev';
        $template->{"prefer-stable"}      = true;

        $template->laravel                = [
            'providers' => [$vendor."\\".$package."\\".$this->sanitizeString($package).'ServiceProvider'],
        ];


        $write = $this->filesystem->put("packages/{$vendor}/{$package}/composer.json", json_encode($template, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        /**
         * @since 1.0
         */
        if ($write == false) {
            $this->error("Could not write composer.json file. Please check permissions");
        }
    }

    /**
     * createServiceProvider
     *
     * Generates a basic service provider scaffold for a new Package project.
     * It defaults to using the Vendor name and Package name to generate
     * namespaces and class names. Could be overwritten manually in the code once
     * generated.
     *
     * Todo: Extract the Templates to an External File if possible?
     *
     * @param $vendor
     * @param $package
     *
     * @author  Olly Warren
     * @package MakePackage Artisan Command
     * @version 1.0
     */
    public function createServiceProvider($vendor, $package)
    {
        //Sanitize the Vendor Name to Construct the Namespace
        $vendorStrip = $this->sanitizeString($vendor);

        if ($vendorStrip == false) {
            $this->error("Invalid Vendor name, exiting!");
        }

        //Sanitize the Package Name to Construct the Namespace
        $packageStrip = $this->sanitizeString($package);

        if ($packageStrip == false) {
            $this->error("Invalid Package name, exiting!");
        }

        //Construct the namespace.
            $namespace = $vendorStrip.'\\'.$packageStrip;

        //Construct a Filename
        $filename = $packageStrip."ServiceProvider.php";

        //Define the template
        $template = "<?php

namespace {$namespace};

use Illuminate\Support\ServiceProvider;

//Add Your Own Package Classes Here!!

class {$packageStrip}ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}";

        $write = $this->filesystem->put("packages/{$vendor}/{$package}/src/{$filename}", $template);

        if ($write == false) {
            $this->error("Could not write {$filename} file. Please check permissions");
        }
    }


    /**
     * createFacade
     *
     * Creates a basic facade accessor and places it in the `{vendor}/{package}/src/facades` directory.
     * Follows the convention of using the Vendor/Package to calculate Namespace and Class name.
     *
     * @param $vendor
     * @param $package
     *
     * @author  Olly Warren
     * @package MakePackage Artisan Command
     * @version 1.0
     */
    public function createFacade($vendor, $package)
    {
        //Sanitize the Vendor Name to Construct the Namespace
        $vendorStrip = $this->sanitizeString($vendor);
        if ($vendorStrip == false) {
            $this->error("Invalid Vendor name, exiting!");
        }

        //Sanitize the Package Name to Construct the Namespace
        $packageStrip = $this->sanitizeString($package);

        if ($packageStrip == false) {
            $this->error("Invalid Package name, exiting!");
        }

        //Construct the namespace.
        $namespace = $vendorStrip.'\\'.$packageStrip.'\Facades';

        //Define the Template
        $template = "<?php

namespace {$namespace};
use Illuminate\Support\Facades\Facade;

class {$packageStrip} extends Facade {

    /**
     * getFacadeAccessor
     *
     * NOTE: MUST BE PAIRED WITH A SERVICE PROVIDER
     * TWEAK FACADE ACCESSOR TO MATCH THE SERVICE PROVIDER REGISTRATION.
     */
    protected static function getFacadeAccessor()
    {
        return '{$packageStrip}';
    }

}";

        $write = $this->filesystem->put("packages/{$vendor}/{$package}/src/facades/{$packageStrip}.php", $template);

        if ($write == false) {
            $this->error("Could not write {$packageStrip}.php facade file. Please check permissions");
        }
    }

    /**
     * createUnitTesting
     *
     * Creates the assets to enable the PHPUnit Unit testing.
     *
     * @param $vendor
     * @param $package
     *
     * @author  Olly Warren
     * @package MakePackage Artisan Command
     * @version 1.0
     */
    public function createUnitTesting($vendor, $package)
    {
        //Update Composer JSON file and add in PHP unit to the Dev Dependencies
        $handle = json_decode($this->filesystem->read("packages/{$vendor}/{$package}/composer.json"));
        $handle->{"require-dev"}->{"phpunit/phpunit"} = "^6.2";

        //Write the changes back to the Composer.json
        $update = $this->filesystem->update("packages/{$vendor}/{$package}/composer.json", json_encode($handle, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        //create phpunit.xml

        $template = '<?xml version="1.0" encoding="UTF-8"?>
<phpunit
        bootstrap="vendor/autoload.php"
        backupGlobals="false"
        backupStaticAttributes="false"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        syntaxCheck="false"
>
    <testsuites>
        <testsuite name="Package Test Suite">
            <directory suffix=".php">./tests/</directory>
        </testsuite>
    </testsuites>
</phpunit>
        ';

        $write = $this->filesystem->put("packages/{$vendor}/{$package}/phpunit.xml", $template);

        if ($write == false) {
            $this->error("Could not write phpunit.xml file. Please check permissions");
        }

        //create tests directory
        $create_tests_dir = $this->filesystem->createDir("packages/{$vendor}/{$package}/tests/");

        //create example test
        $test_template = '<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase {

    /**
     * Example Test Suite
     * @subpackage tests
     * @version 1.0
     */
    public function testReturnsTrue()
    {
        $this->assertTrue(true);
    }
}

        ';

        $write = $this->filesystem->put("packages/{$vendor}/{$package}/tests/ExampleTest.php", $test_template);

        if ($write == false) {
            $this->error("Could not write ExampleTest.php file. Please check permissions");
        }

        $this->line("
        /****************** IMPORTANT NOTE ********************/
        
        PLEASE RUN A COMPOSER UPDATE IN YOUR NEWLY CREATED 
        PACKAGE PROJECT FILE TO INSTALL PHPUNIT
        WE HAVE ALREADY ADDED IT TO THE COMPOSER JSON FILE!
        
        YOU'RE WELCOME! ;)
        
        /****************** -------------- ********************/
        ");
    }

    /**
     * sanitizeString
     *
     * Utility method to strip non alpha characters from the string.
     *
     * @param $string
     *
     * @return bool|mixed
     * @author  Olly Warren
     * @package MakePackage Artisan Command
     * @version 1.0
     */
    public function sanitizeString($string)
    {
        $sring = strtolower($string);
        if (preg_match_all("/[a-zA-Z0-9]+/", $string, $matches) > 0) {
            $string = str_replace( ' ', '', ucwords(implode( ' ', $matches[0])));
            return $string;
        }

        return false;
    }

}
