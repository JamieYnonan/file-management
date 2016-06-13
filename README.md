# file-management
This library can import files from a url or move, copy and rename an existing file on the server.\
Esta libreria puede importar archivos desde una url o mover, copiar y renombrar un archivo existente en el servidor.\
Questa libreria può imporatare file da una url oppure spostare, copiare e rinominare un file esistente nel server.

# example
```php
$imgLocal = new File(__DIR__ .'/file.jpg'); //file
$imgUrl = new File('http://site.com/files/image.png'); //link
```
validate mime (options - recommend)
```php
//return bool
$imgLocal->validateMime('image/jpge');
$imgUrl->validateMime('image/png');
```
save file
```php
first set the path to save the file
$imgLocal->setPath(__DIR__ '/files');
$imgUrl->setPath('/path/local/where/save/file');

$imgLocal->save();
$imgUrl->save('new-name'); //save and change name
```
copy, move, rename (Only allowed for internal files -on the server-)
```php
//return new instance of File
$newImage = $imgLocal->copy('/same/path', 'new-name'); //copy with another name
$newImage2 = $newImage->copy('/new/path2'); //copy in another folder with the same name

$newImage2->move('/other/path'); //return bool

$newImage2->rename('new-name-file'); //return bool
```