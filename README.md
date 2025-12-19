# Azure Blob Storage for PHP

A modern PHP library for interacting with Azure Blob Storage, designed for seamless integration with Symfony and Laravel applications.

## Features

- 🚀 **Simple API** - Intuitive methods for common blob operations
- 📦 **Framework Agnostic** - Works with Symfony, Laravel, or any PHP project
- 🔄 **Version Control** - Built-in blob versioning support (custom implementation for Azurite)
- 📁 **Container Management** - Create, delete, and check container existence
- 📊 **Metadata Support** - Set and retrieve custom metadata on blobs
- 🌊 **Stream Support** - Upload and download large files using streams
- 🔐 **Secure Authentication** - Shared Key authentication using azure-oss/storage

## Requirements

- PHP 8.1 or higher
- Guzzle HTTP client 7.0+
- Azure Storage account or Azurite emulator

## Installation

Install via Composer:

```bash
composer require axinter/azureblobstorage
```

## Configuration

### Basic Setup

Create a configuration object using your Azure Storage connection string:

```php
use AxInter\AzureBlobStorage\Config;
use AxInter\AzureBlobStorage\FileSystem;

$connectionString = 'DefaultEndpointsProtocol=https;AccountName=your-account;AccountKey=your-key;EndpointSuffix=core.windows.net';
$config = new Config($connectionString);
$fileSystem = new FileSystem($config, 'your-container-name');
```

### Azurite (Local Development)

```php
$connectionString = 'DefaultEndpointsProtocol=http;AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1';
$config = new Config($connectionString);
$fileSystem = new FileSystem($config, 'test-container');
```

## Usage

### Container Operations

```php
// Create a container
$fileSystem->createContainer();

// Check if container exists
if ($fileSystem->containerExists()) {
    echo "Container exists!";
}

// Delete a container
$fileSystem->deleteContainer();
```

### File Operations

#### Upload Files

```php
// Write string content
$fileSystem->write('path/to/file.txt', 'Hello, Azure!');

// Upload from stream (efficient for large files)
$stream = fopen('/path/to/large-file.pdf', 'r');
$fileSystem->writeStream('documents/large-file.pdf', $stream);
fclose($stream);
```

#### Download Files

```php
// Read file content as string
$content = $fileSystem->read('path/to/file.txt');

// Download as stream
$stream = $fileSystem->readStream('documents/large-file.pdf');
file_put_contents('/local/path/file.pdf', $stream);
```

#### File Management

```php
// Check if file exists
if ($fileSystem->exists('path/to/file.txt')) {
    echo "File exists!";
}

// Get file information
$size = $fileSystem->getSize('path/to/file.txt');
$mimeType = $fileSystem->getMimeType('path/to/file.txt');
$url = $fileSystem->getUrl('path/to/file.txt');

// Copy file
$fileSystem->copy('source.txt', 'destination.txt');

// Move file
$fileSystem->move('old-path.txt', 'new-path.txt');

// Delete file
$fileSystem->delete('path/to/file.txt');
```

### List Files

```php
// List all files in container
$files = $fileSystem->list();

// List files with prefix
$files = $fileSystem->list('documents/');

foreach ($files as $file) {
    echo "Name: {$file['name']}\n";
    echo "Size: {$file['size']} bytes\n";
    echo "Last Modified: {$file['last_modified']}\n";
    echo "Content Type: {$file['content_type']}\n";
}
```

### Metadata

```php
// Set custom metadata
$fileSystem->setMetadata('path/to/file.txt', [
    'author' => 'John Doe',
    'department' => 'Engineering',
    'version' => '1.0'
]);

// Retrieve metadata
$metadata = $fileSystem->getMetadata('path/to/file.txt');
echo $metadata['author']; // John Doe
```

### Version Control

The library includes custom version control for blob storage (particularly useful with Azurite which doesn't support native versioning):

```php
// Write operations automatically create versions
$fileSystem->write('document.txt', 'Version 1');
$fileSystem->write('document.txt', 'Version 2'); // Previous version saved automatically

// List all versions
$versions = $fileSystem->listVersions('document.txt');
foreach ($versions as $version) {
    echo "Version ID: {$version['version_id']}\n";
    echo "Is Current: {$version['is_current_version']}\n";
    echo "Modified: {$version['last_modified']}\n";
}

// Get specific version
$oldContent = $fileSystem->getVersion('document.txt', '1703001234');

// Restore previous version (creates a new current version)
$fileSystem->restoreVersion('document.txt', '1703001234');

// Delete a specific version
$fileSystem->deleteVersion('document.txt', '1703001234');
```

## Exception Handling

The library provides specific exceptions for different error scenarios:

```php
use AxInter\AzureBlobStorage\Exceptions\BlobNotFoundException;
use AxInter\AzureBlobStorage\Exceptions\InvalidStreamException;
use AxInter\AzureBlobStorage\Exceptions\VersionNotFoundException;
use AxInter\AzureBlobStorage\Exceptions\AzureBlobStorageException;

try {
    $content = $fileSystem->read('non-existent.txt');
} catch (BlobNotFoundException $e) {
    echo "File not found: " . $e->getMessage();
} catch (AzureBlobStorageException $e) {
    echo "Storage error: " . $e->getMessage();
}
```

## API Reference

### FileSystem Methods

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `write()` | `string $path, string $contents` | `bool` | Write content to a blob |
| `read()` | `string $path` | `?string` | Read blob content |
| `delete()` | `string $path` | `bool` | Delete a blob |
| `exists()` | `string $path` | `bool` | Check if blob exists |
| `copy()` | `string $source, string $dest` | `bool` | Copy a blob |
| `move()` | `string $source, string $dest` | `bool` | Move a blob |
| `list()` | `string $prefix = ''` | `array` | List blobs with optional prefix |
| `getSize()` | `string $path` | `?int` | Get blob size in bytes |
| `getMimeType()` | `string $path` | `?string` | Get blob MIME type |
| `getUrl()` | `string $path` | `string` | Get blob URL |
| `writeStream()` | `string $path, resource $stream` | `bool` | Upload from stream |
| `readStream()` | `string $path` | `resource\|null` | Download as stream |
| `setMetadata()` | `string $path, array $metadata` | `bool` | Set blob metadata |
| `getMetadata()` | `string $path` | `?array` | Get blob metadata |
| `listVersions()` | `string $path` | `array` | List all versions of a blob |
| `getVersion()` | `string $path, string $versionId` | `?string` | Get specific version content |
| `restoreVersion()` | `string $path, string $versionId` | `bool` | Restore a previous version |
| `deleteVersion()` | `string $path, string $versionId` | `bool` | Delete a specific version |

## Testing

Run the test suite:

```bash
composer install
vendor/bin/phpunit
```

## License

MIT License - see LICENSE file for details

## Author

- **Joakim Stenberg** - [AxInter](https://axinter.com)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/axinter/azureblobstorage/issues)
