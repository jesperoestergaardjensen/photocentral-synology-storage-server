<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;

class SearchController extends Controller
{
    private LinuxFileRepository $linux_file_repository;
    private PhotoRepository $photo_repository;
    private DatabaseConnection $database_connnection;

    public function __construct(LinuxFileRepository $linux_file_repository, PhotoRepository $photo_repository, DatabaseConnection $database_connnection)
    {
        $this->linux_file_repository = $linux_file_repository;
        $this->photo_repository = $photo_repository;
        $this->database_connnection = $database_connnection;
    }

    public function run(bool $testing = false): void
    {
        $post_data = $this->sanitizePost([
            'search_string' => '',
            'limit' => 10,
            'photo_collection_id_list' => null]
        , $this->database_connnection);

        $this->linux_file_repository->connectToDb();
        $linux_files_seach_result_list = $this->linux_file_repository->search($post_data['search_string'], $post_data['limit'], $post_data['photo_collection_id_list']);

        foreach ($linux_files_seach_result_list as $linux_file) {
            $photo_uuid_list[] = $linux_file->getPhotoUuid();
        }
        $this->photo_repository->connectToDb();
        $photo_list = $this->photo_repository->list($photo_uuid_list ?? []);

        foreach ($photo_list as $photo) {
            $photo_list_array[] = $photo->toArray();
        }

        if ($testing === false) {
            header('Content-Type: application/json');
        }
        echo json_encode($photo_list_array ?? []);
    }
}