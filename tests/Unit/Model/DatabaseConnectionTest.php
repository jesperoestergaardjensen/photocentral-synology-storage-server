<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Model;

use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;

class DatabaseConnectionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorAndGetters()
    {
        $expected_host = 'host';
        $expected_username = 'username';
        $expected_password = 'password';
        $expected_database_name = 'database name';

        $database_connection = new DatabaseConnection($expected_host, $expected_username, $expected_password, $expected_database_name);

        $this->assertEquals($expected_host, $database_connection->getHost());
        $this->assertEquals($expected_username, $database_connection->getUsername());
        $this->assertEquals($expected_password, $database_connection->getPassword());
        $this->assertEquals($expected_database_name, $database_connection->getDatabaseName());
    }
}