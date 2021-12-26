<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\LinuxFileDatabaseTable;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

/**
 * @internal
 */
class LinuxFileRepository
{
    private DbEntity $database_table;
    private DatabaseConnection $database_connection;

    public function __construct(DatabaseConnection $database_connection)
    {
        $this->database_connection = $database_connection;
    }

    public function connectToDb(): void
    {
        $link = DB::link([
            'host'     => $this->database_connection->getHost(),
            'username' => $this->database_connection->getUsername(),
            'password' => $this->database_connection->getPassword(),
            'dbname'   => $this->database_connection->getDatabaseName(),
        ]);

        $this->database_table = new DbEntity(LinuxFileDatabaseTable::NAME, $link);
    }

    /**
     * @param string     $search_string
     * @param int        $limit
     * @param array|null $allowed_photo_collection_ids
     *
     * @return LinuxFile[]
     */
    public function search(string $search_string, int $limit, array $allowed_photo_collection_ids = null): array
    {
        $linux_files = [];

        if ($allowed_photo_collection_ids === null) {
            $sql = "SELECT * FROM LinuxFile WHERE MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";

        } else {
            $photo_collection_ids = implode("','", $allowed_photo_collection_ids);
            $sql = "SELECT * FROM LinuxFile WHERE " . LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID . " IN ('{$photo_collection_ids}') AND MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";
        }

        $linux_files_data = $this->database_table->runSQL($sql);

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    public function update(LinuxFile $linux_file): void
    {
        $condition = [
            LinuxFileDatabaseTable::ROW_INODE_INDEX => $linux_file->getInodeIndex(),
            LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID => $linux_file->getPhotoCollectionId()
        ];

        $this->database_table->edit($condition,
            [
                LinuxFileDatabaseTable::ROW_FILE_NAME => $linux_file->getFileName(),
                LinuxFileDatabaseTable::ROW_FILE_PATH => $linux_file->getFilePath(),
            ]
        );
    }

    public function delete(string $synology_photo_collection_id, int $inode_index): void
    {
        $table_name = LinuxFileDatabaseTable::NAME;
        $where_clause = LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID . " = '{$synology_photo_collection_id}' AND " . LinuxFileDatabaseTable::ROW_INODE_INDEX . " = {$inode_index}";
        $this->database_table->runSQL("DELETE FROM {$table_name} WHERE {$where_clause};");
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    public function getByInode(int $inode_index, string $synology_photo_collection_id): LinuxFile
    {
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_INODE_INDEX . " = $inode_index")
            ->addWhere(LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID . " = '$synology_photo_collection_id'")
            ->get());

        if (isset($linux_files_data[0])) {
            return LinuxFile::fromArray($linux_files_data[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Linux file with inode_index = $inode_index and photo collection id $synology_photo_collection_id");
        }
    }

    public function getByPhotoUuid(string $photo_uuid, string $synology_photo_collection_id): LinuxFile
    {
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_PHOTO_UUID . " = '$photo_uuid'")
            ->addWhere(LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID . " = '$synology_photo_collection_id'")
            ->get());

        if (isset($linux_files_data[0])) {
            return LinuxFile::fromArray($linux_files_data[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Linux file with photo_uuid = $photo_uuid and photo collection id $synology_photo_collection_id");
        }
    }

    /**
     * @param int $inode_index
     *
     * @return LinuxFile[]
     */
    public function list(int $inode_index): array
    {
        $linux_files = [];
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_INODE_INDEX . " = $inode_index")
            ->get());

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }
        return $linux_files;
    }

    public function bulkAdd(array $linux_files_to_bulk_insert, int $bulk_size = 1000): void
    {
        $table_name = LinuxFileDatabaseTable::NAME;
        $table_columns =
            LinuxFileDatabaseTable::ROW_FILE_UUID . ',' .
            LinuxFileDatabaseTable::ROW_PHOTO_UUID . ',' .
            LinuxFileDatabaseTable::ROW_ROW_ADDED_DATA_TIME . ',' .
            LinuxFileDatabaseTable::ROW_PHOTO_COLLECTION_ID . ',' .
            LinuxFileDatabaseTable::ROW_INODE_INDEX . ',' .
            LinuxFileDatabaseTable::ROW_LAST_MODIFIED_DATE . ',' .
            LinuxFileDatabaseTable::ROW_FILE_NAME . ',' .
            LinuxFileDatabaseTable::ROW_IMPORTED . ',' .
            LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME . ',' .
            LinuxFileDatabaseTable::ROW_FILE_PATH;

        while (count($linux_files_to_bulk_insert) !== 0) {
            // Insert in portions of bulk size - default 1000
            $sql_values = '';
            for ($count = 0; $count < $bulk_size && count($linux_files_to_bulk_insert) == ! 0; $count++) {

                /** @var LinuxFile $linux_file */
                $linux_file = array_shift($linux_files_to_bulk_insert);
                $row_added_date_time = time();

                $import_date = $linux_file->getImportDate() ? "'{$linux_file->getImportDate()}'" : 'NULL';
                $photo_uuid = $linux_file->getPhotoUuid() ? "'{$linux_file->getPhotoUuid()}'" : 'NULL';
                $is_imported = (int) $linux_file->isImported();

                $sql_values .= "(
                    '{$linux_file->getFileUuid()}',                
                    {$photo_uuid},                
                    {$row_added_date_time}, 
                    '{$linux_file->getPhotoCollectionId()}',
                    {$linux_file->getInodeIndex()},
                    {$linux_file->getLastModifiedDate()},
                    '{$linux_file->getFileName()}',
                    {$is_imported},
                    {$import_date},
                    '{$linux_file->getFilePath()}'
                ),";
            }

            $sql_values = rtrim($sql_values, ','); // strip last comma
            $this->database_table->runSQL("INSERT INTO {$table_name} ({$table_columns}) VALUES {$sql_values};");
        };
    }
}
