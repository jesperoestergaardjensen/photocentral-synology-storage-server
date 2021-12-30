<?php

namespace PhotoCentralSynologyStorageServer;

use mindplay\unbox\Container;
use mindplay\unbox\ContainerFactory;
use PhotoCentralSynologyStorageServer\Controller\DisplayPhotoController;
use PhotoCentralSynologyStorageServer\Controller\GetPhotoController;
use PhotoCentralSynologyStorageServer\Controller\GetPhotoPathController;
use PhotoCentralSynologyStorageServer\Controller\ListPhotoCollectionsController;
use PhotoCentralSynologyStorageServer\Controller\ListPhotosController;
use PhotoCentralSynologyStorageServer\Controller\SearchController;
use PhotoCentralSynologyStorageServer\Factory\PhotoFactory;
use PhotoCentralSynologyStorageServer\Factory\PhotoUrlFactory;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\PhotoImportResult;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;
use PhotoCentralSynologyStorageServer\Service\PhotoImportService;
use PhotoCentralSynologyStorageServer\Service\PhotoRetrivalService;

class Provider
{
    private Container $di_container;
    private DatabaseConnection $database_connection;
    private string $base_photo_path;
    private string $image_cache_path;

    public function __construct(
        DatabaseConnection $database_connection,
        string $base_photo_path,
        string $image_cache_path
    ) {
        $this->database_connection = $database_connection;
        $this->base_photo_path = $base_photo_path;
        $this->image_cache_path = $image_cache_path;
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

    public function runController(string $controller_class, bool $testing =  false)
    {
        /**
         * @var Controller $controller
         */
        $controller = $this->di_container->get($controller_class);

        if ($testing) {
            ob_start();
            $controller->run($testing);
            return ob_get_clean();
        }
        $controller->run($testing);
    }

    private function registerDependencies(): ContainerFactory
    {
        $container_factory = new ContainerFactory();

        $container_factory->register(DatabaseConnection::class, function () {
            return $this->database_connection;
        });

        $this->registerRepositories($container_factory);
        $this->registerControllers($container_factory);
        $this->registerServices($container_factory);
        $this->registerFactories($container_factory);

        $container_factory->register(PhotoImportService::class);

        return $container_factory;
    }

    private function registerRepositories(ContainerFactory $container_factory): void
    {
        $container_factory->register(SynologyPhotoCollectionRepository::class);
        $container_factory->register(LinuxFileRepository::class);
        $container_factory->register(PhotoRepository::class);
    }

    private function registerControllers(ContainerFactory $container_factory): void
    {
        $container_factory->register(SearchController::class);
        $container_factory->register(GetPhotoPathController::class);
        $container_factory->register(ListPhotosController::class);
        $container_factory->register(ListPhotoCollectionsController::class);
        $container_factory->register(GetPhotoController::class);
        $container_factory->register(DisplayPhotoController::class);
    }

    private function registerServices(ContainerFactory $container_factory)
    {
        $container_factory->register(PhotoRetrivalService::class, function() {
            return new PhotoRetrivalService($this->base_photo_path, $this->image_cache_path);
        });
    }

    private function registerFactories(ContainerFactory $container_factory)
    {
        $container_factory->register(PhotoFactory::class);
        $container_factory->register(PhotoUrlFactory::class, function() {
            return new PhotoUrlFactory('http://photocentral-synology-storage-server/api/');
        });
    }
}
