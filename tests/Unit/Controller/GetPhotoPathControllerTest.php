<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use GuzzleHttp\Client;
use PhotoCentralStorage\Model\ImageDimensions;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Controller\GetPhotoPathController;
use PhotoCentralSynologyStorageServer\Controller\SearchController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class GetPhotoPathControllerTest extends TestCase
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

        self::$provider = new Provider(
            self::$database_connection,
            self::getDataFolder() . '/photos/',
            self::getPublicFolder() . '/photos/cache/'
        );
        self::$provider->initialize();
    }

    public function testRunController()
    {
        $photo_uuid = 'c3db925a9c3f19f6285f7038dcd9844e';
        $photo_collection_id = '11efa610-5378-4964-b432-d891aef00eb9';
        $image_dimensions = ImageDimensions::createThumb();

        // Prepare
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');
        $expected_photo_path = 'photos/cache/' . $image_dimensions->getId() . "/" . $photo_uuid . '.jpg';

        // Simulate post request - prepare data for controller
        $_POST['photo_uuid'] = $photo_uuid;
        $_POST['photo_collection_id'] = $photo_collection_id;
        $_POST['image_dimensions'] = $image_dimensions->toArray();

        // Execute
        $json_content = self::$provider->runController(GetPhotoPathController::class, true); // Code coverage / debugging
        $actual_photo_path = json_decode($json_content, true);

        // Test
        $this->assertEquals($expected_photo_path, $actual_photo_path, 'Correct photo path was returned');
    }
}
