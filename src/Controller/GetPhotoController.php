<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;

class GetPhotoController extends Controller
{
    private DatabaseConnection $database_connection;
    private PhotoRepository $photo_repository;

    public function __construct(
        DatabaseConnection $database_connection,
        PhotoRepository $photo_repository
    ) {
        $this->database_connection = $database_connection;
        $this->photo_repository = $photo_repository;
    }

    public function run(bool $testing = false): void
    {
        $post_data = $this->sanitizePost(['photo_uuid' => null, 'photo_collection_id' => null],
            $this->database_connection);

        $this->photo_repository->connectToDb();
        $photo = $this->photo_repository->get($post_data['photo_uuid'], $post_data['photo_collection_id']);

        if ($testing === false) {
            header('Content-Type: application/json');
        }

        echo json_encode($photo);
    }
}