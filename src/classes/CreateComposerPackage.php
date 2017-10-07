<?php

namespace Ollywarren\Makepackage\Classes;

use Illuminate\Console\Command;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use SebastiaanLuca\StubGenerator\StubGenerator;

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


    protected $adaptor;
    protected $filesystem;
    protected $stubLocation;


    /**
     * CreateComposerPackage constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->adaptor      = new Local(base_path(), 1, Local::SKIP_LINKS, []);
        $this->filesystem   = new Filesystem($this->adaptor);
        $this->stubLocation = __DIR__ . '/stubs/';
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
        $author_name            = $this->ask('What is the Author name?');
        $author_email           = $this->ask('What is the Author email?');


        //Lets create the directory structure we need within Laravel.
        $this->createBaseStructure($vendor_name, $package_name, $package_description);


        //Service Provider required ?
        $service_provider = $this->choice('Do you need a Laravel Service Provider for this Package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($service_provider !== 'No') {
            $serviceProviderGenerator = new StubGenerator($this->stubLocation.'ServiceProvider.php.stub', "packages/{$vendor_name}/{$package_name}/src/{$this->sanitizeString($package_name)}ServiceProvider.php");
            $serviceProviderGenerator->render([
                ':NAMESPACE:'    => $this->sanitizeString($vendor_name).'\\'.$this->sanitizeString($package_name),
                ':PACKAGE_NAME:' => $this->sanitizeString($package_name)
            ]);
        }

        //Facade
        $facade = $this->choice('Do you need a Laravel Facade for this Package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($facade != 'No') {
            //Generate a Facade
            $facadeGenerator = new StubGenerator(
                $this->stubLocation.'Facade.php.stub',
                "packages/{$vendor_name}/{$package_name}/src/facades/{$this->sanitizeString($package_name)}.php"
            );
            $facadeGenerator->render([
                ':NAMESPACE:'   => $this->sanitizeString($vendor_name).'\\'.$this->sanitizeString($package_name).'\Facades',
                ':PACKAGE_NAME' => $this->sanitizeString($package_name)
            ]);

        }

        //Testing
        $testing = $this->choice('Do you need to unit test this package?', ['1' => 'Yes', '2' =>'No'], '2');

        if ($testing != 'No') {
            //Generate phpunit.xml
            $phpunitGenerator = new StubGenerator(
                $this->stubLocation.'phpunit.xml.stub',
                "packages/{$vendor_name}/{$package_name}/phpunit.xml"
            );
            $phpunitGenerator->render([]);

            //Generate Unit Test Example
            $unitTestGenerator = new StubGenerator(
                $this->stubLocation.'ExampleTest.php.stub',
                "packages/{$vendor_name}/{$package_name}/tests/ExampleTest.php"
            );
            $unitTestGenerator->render([]);
        }

        //Generate gitignore
        $gitignoreGenerator = new StubGenerator(
            $this->stubLocation.'gitignore.stub',
            "packages/{$vendor_name}/{$package_name}/.gitignore"
        );
        $gitignoreGenerator->render([]);

        //Generate Readme
        $readmeGenerator = new StubGenerator(
            $this->stubLocation.'README.md.stub',
            "packages/{$vendor_name}/{$package_name}/README.md"
        );
        $readmeGenerator->render([]);



        // Generate the composer.json file.
        $composerGenerator = new StubGenerator($this->stubLocation.'composer.json.stub', "packages/{$vendor_name}/{$package_name}/composer.json");

        $composerGenerator->render([
            ':AUTHOR_NAME:'         => $author_name,
            ':AUTHOR_EMAIL:'        => $author_email,
            ':VENDOR_NAME:'         => $vendor_name,
            ':PACKAGE_NAME:'        => $package_name,
            ':PACKAGE_DESCRIPTION:' => $package_description,
            ':NAMESPACE:'           => $this->sanitizeString($vendor_name).'\\'.$this->sanitizeString($package_name),
            ':PROVIDER_NAMESPACE:'  => ($service_provider !== 'No') ? $this->sanitizeString($vendor_name).'\\'.$this->sanitizeString($package_name).'\\'.$this->sanitizeString($package_name).'ServiceProvider' : '',
            ':FACADE_NAMESPACE:'    => ($facade !== 'No') ? $this->sanitizeString($vendor_name).'\\'.$this->sanitizeString($package_name).'\\Facades\\'.$this->sanitizeString($package_name) : '',
            ':REQUIREMENTS:'        => ($testing !== 'No') ? '"phpunit/phpunit":"^6.2"' : ''
        ]);


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

        if ($structure == false) {
                $this->error("There was an error creating the directory structure. Please check your permissions.");
        }
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
        $string = strtolower($string);
        if (preg_match_all("/[a-zA-Z0-9]+/", $string, $matches) > 0) {
            $string = str_replace(' ', '', ucwords(implode(' ', $matches[0])));
            return $string;
        }

        return false;
    }

}
