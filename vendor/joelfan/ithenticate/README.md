# PHP [iThenticate](http://www.ithenticate.com/)
A library to use **iThenticate** API easier and faster, to check and prevent plagiarism.
This is a fork from bsobbe/ithenticate

### Installation
You can install via **composer** package manager with the following command:

```
composer.phar require joelfan/ithenticate "*"
```

Or add the following to your **composer.json** file:

```
"require": {
        "joelfan/ithenticate": "*"
},
```

### Usage
Once the installation is completed, simply use the library with:
```php
use joelfan\ithenticate\Ithenticate;
```
You will be able to use the library by creating instance of the ```Ithenticate``` class, make sure you pass your iThenticate API **username** and **password** to the constructor (You might need SSL to connect to the API):
```php
$ithenticate = new Ithenticate("Your username", "Your password");
```
Or:
```php
$ithenticate = new Ithenticate("Your username", "Your password", $proxyObject);
// where 
// * $proxyObject->proxyName (string) *
// * $proxyObject->proxyPort (integer) *
```

After all simply call each method you want to use with passing the required parameters, and the library will take care of the rest.

I strongly suggest to read the [iThenticate API Guide](http://www.ithenticate.com/hs-fs/hub/92785/file-1383985272-pdf/iTh_documentation/iThenticate_API_Manual.pdf?t=1488585417195) before using the library and its methods.

### Methods

#### Submit document
Here is one simple example to send new document:
```php
$ithenticate = new \bsobbe\ithenticate\Ithenticate("username", "password");
//The value in result variable is the document_id of the inserted document.
$result = $ithenticate->submitDocument(
                "Cloud Computing",
                "Sobhan",
                "Bagheri",
                "CloudComputingEssay.pdf", //File name from the object of the uploaded temp file.
                $content, //Document content fetched with php file_get_contents() function from the document file.
                649216 //Folder number to store document (You can get folder number from last part of ithenticate panel URL).
          );
```

#### Get document data
```php
$ithenticate = new \bsobbe\ithenticate\Ithenticate("username", "password");
$result = $ithenticate->documentGetRequest(12345);
// Since we are requesting 1 document, there should be 1 document only in the response.
$document = reset($result['documents']);
$is_pending = $document['is_pending']; // If the report is pending.
$document_id = $document['id'];
$processed_time = $document['processed_time']; // The time the report has been created.
$percent_match = $document['percent_match']; // The percentage match for the document.
$title = $document['title']; // The submitted title of the document.
$uploaded_time = $document['uploaded_time']; // The time the document was uploaded.

// Also, $document['folder'] is available containing information related to the folder that the document is submitted
// into.
```

#### Get report data
```php
$ithenticate = new \bsobbe\ithenticate\Ithenticate("username", "password");
$result = $ithenticate->reportGetRequest(98765, 1, 1, 1); // The report ID.

$view_only_url = $result['view_only_url'];
$view_only_expires = $result['view_only_expires'];
$report_url = $result['report_url'];
```

### Contribute
Feel free to **contribute** and add new methods based on ithenticate's [API Guide](https://help.turnitin.com/ithenticate/ithenticate-developer/api/api-guide.htm#APImethodreference)

Add method usage instructions in ReadMe.md

### Contribution 2022 06
-- Added methods for User management:
* function fetchUserList() * no argument, returns a list of users; 
* function addUser($userEmail, $userPass, $firstName, $lastName) * returns ID of the new user defined or -1; 
* function dropUser($id) * arguments is userId; returns return code of the operation; 
* function shareSubmissionFolder($folderId, $shared_with) * arguments are folderId and array of userId; returns return code of the operation; 

-- Added proxy support:
When instatiating, you can now pass a proxy object in order to manage phpxmlrpc communication via proxy. 
* new \bsobbe\ithenticate\Ithenticate($user, $pass, $proxyObject) * where $proxyObject is 
* $proxyObject->proxyName (string) *
* $proxyObject->proxyPort (integer) *

-- Modified requirements for phpxmlrpc/phpxmlrpc:
Changed * phpxmlrpc/phpxmlrpc": "4.0" * to * "phpxmlrpc/phpxmlrpc": "*" *
In this way the last version of phpxmlrpc/phpxmlrpc is taken and php8 support is guaranteed.

