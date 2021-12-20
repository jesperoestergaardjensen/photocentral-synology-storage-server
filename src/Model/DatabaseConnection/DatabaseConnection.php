<?php

namespace PhotoCentralSynologyStorageServer\Model\DatabaseConnection;

interface DatabaseConnection
{
    public function getHost(): string;

    public function getUsername(): string;

    public function getPassword(): string;

    public function getDatabaseName(): string;
}