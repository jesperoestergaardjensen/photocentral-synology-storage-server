<?php

namespace PhotoCentralSynologyStorageServer\Tests;

use mysqli;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\LinuxFileDatabaseTable;
use jesperoestergaardjensen\MyDB\DB;
use jesperoestergaardjensen\MyDB\DbEntity;

class TestDatabaseService
{
    private DatabaseConnection $database_connection;
    private mysqli $database_link;
    private DbEntity $linux_file_table;

    public function __construct()
    {
        $this->database_connection = new DatabaseConnection('127.0.0.1', 'tester', 'Ziu2Uv1o$Ziu2Uv1o', 'photocentral-synology-storage-server-test');

/*
        $this->database_connection = new DatabaseConnection(
            $GLOBALS['mysql_host'],
            $GLOBALS['mysql_user'],
            $GLOBALS['mysql_pass'],
            $GLOBALS['mysql_db']
        );
 */

        $this->database_link = DB::link([
            'host'     => $this->database_connection->getHost(),
            'username' => $this->database_connection->getUsername(),
            'password' => $this->database_connection->getPassword(),
            'dbname'   => $this->database_connection->getDatabaseName(),
        ]);

        $this->linux_file_table = new DbEntity(LinuxFileDatabaseTable::NAME, $this->database_link);
    }

    public function installDatabase(): DatabaseConnection
    {
        $this->setupLinuxFileTable();
        $this->setupSynologyPhotoCollectionTable();
        $this->setupPhotoTable();

        return $this->database_connection;
    }

    public function uninstallDatabase()
    {
        $file_name = (dirname(__DIR__, 1) . '/sql/uninstall.sql');
        $this->linux_file_table->runScript(file_get_contents($file_name));
    }

    public function emptyDatabaseTable(string $database_table_name)
    {
        $this->linux_file_table->runSQL('TRUNCATE ' . $database_table_name);
    }

    public function addLinuxFileFixture(string $fixture_filename)
    {
        $file_name = (dirname(__DIR__) . "/tests/sql/$fixture_filename");
        $this->linux_file_table->runScript(file_get_contents($file_name));
    }

    private function setupLinuxFileTable(): void
    {
        $file_name = (dirname(__DIR__) . '/sql/LinuxFile.sql');
        $sql_string = file_get_contents($file_name);
        $this->linux_file_table->runScript($sql_string);
    }

    private function setupSynologyPhotoCollectionTable(): void
    {
        $file_name = (dirname(__DIR__) . '/sql/SynologyPhotoCollection.sql');
        $sql_string = file_get_contents($file_name);
        $this->linux_file_table->runScript($sql_string);
    }

    private function setupPhotoTable(): void
    {
        $file_name = (dirname(__DIR__) . '/sql/Photo.sql');
        $sql_string = file_get_contents($file_name);
        $this->linux_file_table->runScript($sql_string);
    }
}
