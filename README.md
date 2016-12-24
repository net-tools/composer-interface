# net-tools/composer-interface

## PHP interface to Composer when SSH is not available on your host

The package contains an helper class to interface with Composer when your host (or hosting plan) does not support SSH (direct access).

With this simple PHP class, you can execute composer command through shell calls and get the output of the command in a string. A simple GUI is also provided in the demo/ folder.



### Setup instructions

Download the ZIP release and upload it's content to your host.

Since you are looking for a Composer interface, we assume that you can't run Composer at the moment (no SSH access), that's why you have to manually download the library and upload it on your website.



### How to use ?

In order to use the PHP interface to Composer, *and after having uploaded the ZIP release to your host*, you must :

1. create a client config file in JSON format (or define a PHP associative array) ; see below for required parameters,
1. include the `src/autoload.php` file (this is a custom autoload file, since Composer is not installed yet, you don't have it's autoload mechanism available),
1. in a PHP script, create a `ComposerInterface` object with a config object and the path of your Composer project,
1. call one of the supported Composer command as methods of the `ComposerInterface` object.


The JSON client config file or the associative config array **MUST** define the following values :

Property                  | Description
--------------------------|---------------
composer_phpbin           | Path to your PHP binary ; for example : `/usr/local/php7.0/bin/php`
composer_home             | Path to the Composer home folder ; usually, this is the root of your host account (web root), or one level above.


Then create a config instance and pass it to the `ComposerInterface` constructor :

```php
// include script (you may point the inclusion to the right path to src/autoload.php)
include_once "autoload.php";

// create config object
$config = Config::fromJSON(__DIR__ . '/composer.config.json');
// OR
// $config = Config::fromArray(array('composer_phpbin'=>'usr/local/php7.0/bin/php', 'composer_home'=>$_SERVER['DOCUMENT_ROOT']));

// create interface and set the composer project to be in folder LIBCOMPOSER, directly placed under the web root folder
$composer = new ComposerInterface($config, rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/libcomposer');
```

For example, to do the initial setup of Composer :
```php
$output = $composer->setup();
echo "<pre>$output</pre>";
```

And then to require a package : 
```php
$output = $composer->package_require('net-tools/core');
echo "<pre>$output</pre>";
```

Composer commands have been separated in 3 groups :

- global commands
- commands applied to packages
- commands applied to repositories

Global commands are called with the corresponding method name (eg `$composer->diagnose()`). Package commands are called with the *package_* prefix (eg `$composer->package_update('vendor/name')`). Repositories commands are more specific :

- to add a repository : `$composer->repository_add('path', 'http://url')`
- to remove a repository : `$composer->repository_remove('http://url')`

If you want to send a command not supported by `ComposerInterface`, you may use the `Command` method : `$composer->command('unsupportedcommand vendor/name arguments')`.


### API Reference

A PHPDoc documenting all supported commands is available here : [API Reference](http://net-tools.ovh/api-reference/net-tools/Nettools/ComposerInterface.html).


### Demo 

A simple GUI interface is provided in the demo/ subfolder.

To run the demo, just modify the provided client config file `composer.config.json` (in the demo folder) to point to the PHP binary on your host, and open your browser to the `gui_demo.php` file of the demo folder. If you don't know the path for the PHP binary, you'd better refer to your host support team.

The demo will create a `libc-composerinterface` folder on your web root. If you want another name or path, modify the `PROJECT` constant on the top lines of the demo file.

**Please note that the demo comes with an empty composer project and no Composer package**. The Composer software **MUST** be installed. To do so, you have to hit the SETUP button on the GUI (or call the `setup` method of `ComposerInterface` in a script of your own). This will download Composer and run the install script. A default composer.json will be created. THEN you can play around with require, update, etc.


