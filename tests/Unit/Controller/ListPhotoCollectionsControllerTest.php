<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use PhotoCentralStorage\PhotoCollection;
use PhotoCentralSynologyStorageServer\Controller\ListPhotoCollectionsController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class ListPhotoCollectionsControllerTest extends TestCase
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
        self::$test_database_service->addLinuxFileFixture('list_photo_collections_test_fixture.sql');

        // Simulate post request - prepare data for controller
        $_POST['limit'] = 2;

        // Excute
        $json_content = self::$provider->runController(ListPhotoCollectionsController::class, true); // Code coverage / debugging
        $photo_collection_array_list = json_decode($json_content, true);

        $photo_collection_list = [];
        foreach ($photo_collection_array_list as $photo_collection_array) {
            $photo_collection_list[] = PhotoCollection::fromArray($photo_collection_array);
        }

        $this->assertEquals('id1', $photo_collection_list[0]->getId(), 'The correct photo collection was returned');
        $this->assertEquals('id2', $photo_collection_list[1]->getId(), 'The correct photo collection was returned');

        $this->assertCount(2, $photo_collection_list, 'The correct length og the list is returned');
    }
}
