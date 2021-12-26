<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use GuzzleHttp\Client;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Controller\SearchController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class SearchControllerTest extends TestCase
{
    use TestService;

    private static TestDatabaseService $test_database_service;
    private static DatabaseConnection $database_connection;
    private static Provider $provider;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();

        self::$provider = new Provider(self::$database_connection, '', '');
        self::$provider->initialize();
    }

    public function testRunController()
    {
        $expected_photo_uuid = 'c3db925a9c3f19f6285f7038dcd9844e';

        // Prepare
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');

        // Simulate post request - prepare data for controller
        $_POST['search_string'] = 'SamsungS6';
        $_POST['limit'] = 10;

        // Excute
        $json_content = self::$provider->runController(SearchController::class, true); // Code coverage / debugging
        $photo_array = json_decode($json_content, true);
        $actual_photo = Photo::fromArray($photo_array[0]);

        // Test
        $this->assertEquals($expected_photo_uuid, $actual_photo->getPhotoUuid(), 'Correct photo was found by search');
    }
}
