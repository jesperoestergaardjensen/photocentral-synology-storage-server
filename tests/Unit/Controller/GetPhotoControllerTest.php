<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralStorage\Model\PhotoSorting\BasicSorting;
use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Controller\GetPhotoController;
use PhotoCentralSynologyStorageServer\Controller\ListPhotosController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class GetPhotoControllerTest extends TestCase
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
            'http://photocentral-synology-storage-server/api/',
            self::$database_connection,
            self::getDataFolder() . '/photos/'
        );
        self::$provider->initialize();
        unset($_POST);
    }

    public function testRunController()
    {
        // Prepare
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');

        $expected_photo_uuid = 'c3db925a9c3f19f6285f7038dcd9844e';
        $expected_photo_collection_id = '11efa610-5378-4964-b432-d891aef00eb9';

        // Simulate post request - prepare data for controller
        $_POST['photo_uuid'] = $expected_photo_uuid;
        $_POST['photo_collection_id'] = $expected_photo_collection_id;

        // Excute
        $json_content = self::$provider->runController(GetPhotoController::class, true); // Code coverage / debugging
        $photoArray = json_decode($json_content, true);
        $photo = Photo::fromArray($photoArray);

        $this->assertEquals($expected_photo_uuid, $photo->getPhotoUuid(),
            'The correct photo was returned - photo_uuid check');
        $this->assertEquals($expected_photo_collection_id, $photo->getPhotoCollectionId(),
            'The correct photo was returned - photo_collection_id check');
    }
}
