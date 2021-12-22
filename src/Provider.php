<?php

namespace PhotoCentralSynologyStorageServer;

use mindplay\unbox\Container;
use mindplay\unbox\ContainerFactory;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\PhotoImportResult;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;
use PhotoCentralSynologyStorageServer\Service\PhotoImportService;

class Provider
{
    private Container $di_container;
    private DatabaseConnection $database_connection;

    public function __construct(
        DatabaseConnection $database_connection
    ) {
        $this->database_connection = $database_connection;
    }

    public function initialize()
    {
        $container_factory = $this->registerDependencies();
        $this->di_container = $container_factory->createContainer();
    }

    public function importPhotos(): PhotoImportResult
    {
        /** @var PhotoImportService $photo_import_service */
        $photo_import_service = $this->di_container->get(PhotoImportService::class);
        return $photo_import_service->import();
    }

    private function registerDependencies(): ContainerFactory
    {
        $container_factory = new ContainerFactory();

        $container_factory->register(DatabaseConnection::class, function () {
            return $this->database_connection;
        });

        $this->registerRepositories($container_factory);

        $container_factory->register(PhotoImportService::class);

        return $container_factory;
    }

    private function registerRepositories(ContainerFactory $container_factory): void
    {
        $container_factory->register(SynologyPhotoCollectionRepository::class);
        $container_factory->register(LinuxFileRepository::class);
    }
}