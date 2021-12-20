<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Repository;

use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\SimpleDatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PHPUnit\Framework\TestCase;

class LinuxFileRepositoryTest extends TestCase
{
    private static TestDatabaseService $test_database_service;
    private static SimpleDatabaseConnection $database_connection;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();
    }

    public static function tearDownAfterClass(): void
    {
        // self::$test_database_service->uninstallDatabase();
    }

    public function testSearch()
    {
        // Prepare
        $expected_linux_file_uuid = '2e869946-8be0-4193-a4a2-72ff6b0f5e93';
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');

        // Execute
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();
        $linux_file_found_from_search = $linux_file_repository->search('SamsungS6', 10);

        // Compare
        $this->assertEquals($expected_linux_file_uuid, $linux_file_found_from_search[0]->getFileUuid(),
            'Search returns the correct Linux file ');
    }
}
