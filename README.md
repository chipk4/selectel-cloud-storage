# Selectel cloud storage API (selectel.com) for Laravel 5
[![GitHub release](https://img.shields.io/badge/release-v0.2--beta-blue.svg?style=flat-square)](https://github.com/chipk4/selectel-cloud-storage/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides an easy way to integrate Selectel Cloud Storage API with Laravel 5. Here are some examples of what you can do with the package:

```php

SelectelApi::storageInfo();

//You may use virtual folders like 'newFolder/newFileName.jpg'
SelectelApi::storeFile('container', 'filePath/1243.jpg', 'newFileName.jpg')

SelectelApi::containerInfo('your_container')

SelectelApi::storageContainerList()

/*
* You can use private or public container type
* Public container present by default
* For private container use 
*/
SelectelApi::getFile('container', 'fileName', true);
//For public container
SelectelApi::getFile('container', 'fileName');

SelectelApi::containerFileList('container_name');

//If you want to do something else, you can get an instance of the underlying API:
SelectelApi::getApi();
```

## Installation

You can install this package via Composer using:

```bash
composer require chipk4/selectel
```
You must also install this service provider.

```php
// config/app.php
'providers' => [
    ...
    Chipk4\Selectel\SelectelApiServiceProvider::class,
    ...
];
```
If you want to make use of the facade you must install it as well.

```php
// config/app.php
'aliases' => [
    ..
    'SelectelApi' => Chipk4\Selectel\SelectelApiFacade::class,
];
```

To publish the config file to `app/config/selectel-api.php` run:

```bash
php artisan vendor:publish --provider="Chipk4\Selectel\SelectelApiServiceProvider"
```

This will publish a file `selectel-api.php` in your config directory with the following contents:
```php
return [
    /*
     * This is agreement number from system
     */
    'authUser' => env('SELECTEL_LOGIN'),

    /*
     * Password for cloud storage service.
     * Note: it's different with account password
     */
    'authKey' => env('SELECTEL_PASSWORD'),

    /*
     * API url
     */
    'apiUrl' => 'https://auth.selcdn.ru/',

    /*
     * Default value for request timeout
     */
    'timeout' => 10,

    /*
     * Default storage url
     */
    'storageUrl' => env('SELECTEL_STORAGE_URL', ''),

    /*
     * Response view
     * Can be in json or xml
     */
    'returnView' => env('SELECTEL_RETURN_VIEW', 'json'),
    
];
```

## Usage
#### With Facade

```php
  use SelectelApi;
  
  SelectelApi::getFile('container', 'fileName');
```

#### With Service Container

```php
    use Chipk4\Selectel\CloudStorage;
    
    public function test(CloudStorage $storage) 
    {
        $storage->getFile('container', 'fileName');
    }
```
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.