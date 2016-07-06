Attachments for Elgg
====================
![Elgg 1.9](https://img.shields.io/badge/Elgg-1.9-orange.svg?style=flat-square)
![Elgg 1.10](https://img.shields.io/badge/Elgg-1.10-orange.svg?style=flat-square)
![Elgg 1.11](https://img.shields.io/badge/Elgg-1.11-orange.svg?style=flat-square)
![Elgg 1.12](https://img.shields.io/badge/Elgg-1.12-orange.svg?style=flat-square)

## Features

 * API and actions for attaching files and other entities
 * Form input for uploading file attachments
 * Views for displaying attachments


## Acknowledgements

 * Plugin development has been partially sponsored by [Social Business World](https://socialbusinessworld.org/)

## Usage

### Display an attachment input

```php
echo elgg_view('input/attachments');
```

### Attach uploaded files in an action

```php
hypeapps_attach_uploaded_files($entity, 'upload', [
   'access_id' => $entity->access_id, // change the access level of uploaded files
]);
```

### Attach an object

```php
hypeapps_attach($entity, $attachment);
```

### Display attachments

```php

```php
echo elgg_view('output/attachments', [
	'entity' => $entity,
]);
```
