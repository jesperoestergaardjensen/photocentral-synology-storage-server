<?php

namespace PhotoCentralSynologyStorageServer\Model\DatabaseConnection;

class DatabaseConnection
{
    private string $host;
    private string $username;
    private string $password;
    private string $database_name;

    public function __construct(string $host, string $username, string $password, string $database_name)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database_name = $database_name;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDatabaseName(): string
    {
        return $this->database_name;
    }
}