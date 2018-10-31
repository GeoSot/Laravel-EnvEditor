[![Codacy Badge](https://api.codacy.com/project/badge/Grade/9393e7e8eee54d749745f7115a075ecd)](https://www.codacy.com/app/geo.sotis/LaravelSettings?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=GeoSot/LaravelSettings&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/f494c7292af300b0c7fc/maintainability)](https://codeclimate.com/github/GeoSot/Laravel-EnvEditor/maintainability)
[![License](https://poser.pugx.org/geo-sot/laravel-env-editor/license)](https://packagist.org/packages/geo-sot/laravel-env-editor)
# Laravel .env Editor (plus GUI) 
This Package allows to manage Laravel .env file values on the Fly (add, edit, delete keys), upload another .env or create backups
<br/>
Management can be done through the user interface, or programmatically by using the `EnvEditor` Facade, without breaking the files structure. 
<br/>
The inspiration for this package was -, [Brotzka/laravel-dotenv-editor](https://github.com/Brotzka/laravel-dotenv-editor).

*   [Installation](#installation)
*   [Available Methods](#available_methods)
*   [User Interface](#user_interface)

    ## <a name="installation">Installation:</a>

1. Install package
    ```bash
    composer require geo-sot/laravel-env-editor
    ```
2. Edit config/app.php (*Skip this step if you are using laravel 5.5+*)
    Service provider:
    ```php
    GeoSot\EnvEditor\ServiceProvider::class
    ```
    Class aliases:
    ```php
    'EnvEditor' => GeoSot\\EnvEditor\\Facades\\EnvEditor::class
    ```
 3. Publish assets 
     ```bash
     php artisan vendor:publish --provider=GeoSot\EnvEditor\ServiceProvider     
      ```      
      This will publish all files:
    * config -> env-editor.php
    * views -> resources/views/vendor/geo-sot/env-editor/..
    * lang -> resources/lang/vendor/geo-sot/env-editor.php
      
     Or publish specific tags

    ```bash
     //Publish specific tag
     php artisan vendor:publish --tag=config
     php artisan vendor:publish --tag=translations
     php artisan vendor:publish --tag=views
     
     //Publish specific Tag from this Vendor
     php artisan vendor:publish --provider=GeoSot\EnvEditor\ServiceProvider --tag=config  
 
     ```
     
## <a name="available_methods">Available Methods:</a>

>* getEnvFileContent
>* keyExists
>* getKey
>* addKey
>* editKey
>* deleteKey
>* getAllBackUps
>* upload
>* backUpCurrent
>* getFilePath
>* deleteBackup
>* restoreBackUp

   ```php
     
    EnvEditor::getEnvFileContent($fileName='') 
    // Return The .env Data as Collection.
    // If FileName Is provided it searches inside backups Directory and returns these results
 
    EnvEditor::keyExists($key)
    // Search key existance in .env
    
    EnvEditor::getKey(string $key, $default = null)    
    // Get key value from .env,
 
     EnvEditor::addKey($key, $value, array $options = [])
     // Adds new Key in .env file
     // As options can pass ['index'=>'someNumericIndex'] in order to place the new key after an other and not in the end
 
     EnvEditor::editKey($key, $nvalue)
     // Edits existing key value
 
     EnvEditor::deleteKey($key)    
 
     EnvEditor::getAllBackUps()
     // Returns all Backup files as collection with some info like, created_date, content etc.
 
     EnvEditor::upload(UploadedFile $uploadedFile, $replaceCurrentEnv)
     // Gets an UploadedFile and stores it as backup or as current .env
 
     EnvEditor::backUpCurrent()
     // Backups current .env
 
     EnvEditor::getFilePath($fileName = '')
     // Returns the full path of a backup file. 
     // If $fileName is empty returns the full path of the .env file
 
     EnvEditor::deleteBackup($fileName)
     
 
     EnvEditor::restoreBackUp()
     


 ````
 <br/>
 

## <a name="user_interface">User Interface</a>

User Interface Contains three Tabs 

 -  [Current .env](#current_env)
    * [Add new Key](#add_key)
    * [Edit Key](#edit_key)
    * [Delete new Key](#delete_key)
 - [Backups](#backups)
   * [Backups Index](#backups_index)
   * [Backup file details](#backup_file_details)
 - [Upload](#upload)
 
 <br/>
 
### <a name="current_env">Current .env </a>
![Overview](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/01_currentEnv.png)
<br/>
<br/>
#### <a name="add_key">Add new key</a>
![AddKey](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/02_addKey.png)
<br/>
<br/>
#### <a name="edit_key">Edit key</a>
![EditKey](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/03_editKey.png)
<br/>
<br/>
#### <a name="delete_key">Delete key</a>
![DeleteKey](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/04_deleteKey.png)
<br/>
<br/>
### <a name="backups">Backups</a>
#### <a name="backups_index">Backups Index</a>
![Overview](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/05_backupsIndex.png)
<br/>
<br/>
#### <a name="backup_file_details">Backup file details</a>
![Overview](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/06_backups_details.png)
<br/>
<br/>
### Upload
![Overview](https://raw.githubusercontent.com/GeoSot/Laravel-EnvEditor/master/images/07_upload.png)

   