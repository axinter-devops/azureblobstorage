<?php

namespace AxInter\AzureBlobStorage\Tests;

use AxInter\AzureBlobStorage\Config;
use AxInter\AzureBlobStorage\FileSystem;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    public function testConfigInitialization(): void
    {
        $connectionString = 'DefaultEndpointsProtocol=http;AccountName=devstoreaccount1;AccountKey=fakekey;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1';
        $config = new Config($connectionString);
        
        $this->assertSame('devstoreaccount1', $config->getAccountName());
        $this->assertSame('http://127.0.0.1:10000/devstoreaccount1', $config->getBlobEndpoint());
    }
}
