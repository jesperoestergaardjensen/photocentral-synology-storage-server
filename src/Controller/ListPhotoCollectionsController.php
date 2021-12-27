<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;

class ListPhotoCollectionsController extends Controller
{
    private DatabaseConnection $database_connection;
    private SynologyPhotoCollectionRepository $synology_photo_collection_repository;

    public function __construct(
        DatabaseConnection $database_connection,
        SynologyPhotoCollectionRepository $synology_photo_collection_repository
    ) {
        $this->database_connection = $database_connection;
        $this->synology_photo_collection_repository = $synology_photo_collection_repository;
    }

    public function run(bool $testing = false): void
    {
        $post_data = $this->sanitizePost(['limit' => 25], $this->database_connection);

        $this->synology_photo_collection_repository->connectToDb();
        $full_list = $this->synology_photo_collection_repository->list();

        $adjusted_list = array_slice($full_list, 0, $post_data['limit']);

        echo json_encode($adjusted_list);
    }
}
