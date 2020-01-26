<?php

namespace PhpLab\Eloquent\Fixture\Services;

use Illuminate\Support\Collection;
use PhpLab\Eloquent\Fixture\Repositories\DbRepository;
use PhpLab\Eloquent\Fixture\Repositories\FileRepository;
use PhpLab\Eloquent\Migration\Repositories\HistoryRepository;

class FixtureService
{

    private $dbRepository;
    private $fileRepository;
    private $excludeNames = [
        HistoryRepository::MIGRATION_TABLE_NAME,
    ];

    public function __construct(DbRepository $dbRepository, FileRepository $fileRepository)
    {
        $this->dbRepository = $dbRepository;
        $this->fileRepository = $fileRepository;
    }

    public function allForDelete()
    {
        $collection = $this->dbRepository->allTables();
        return $collection;
    }

    public function allFixtures()
    {
        $collection = $this->fileRepository->allTables();
        return $this->filterByExclude($collection);
    }

    public function allTables(): Collection
    {
        $collection = $this->dbRepository->allTables();
        return $this->filterByExclude($collection);
    }

    public function dropAllTables()
    {
        $this->dbRepository->dropAllTables();
        $this->dbRepository->dropAllViews();
        //$this->dbRepository->dropAllTypes();
    }

    public function dropTable($name)
    {
        $this->dbRepository->deleteTable($name);
    }

    public function importTable($name)
    {
        $data = $this->fileRepository->loadData($name);
        $this->dbRepository->saveData($name, $data);
    }

    public function exportTable($name)
    {
        $collection = $this->dbRepository->loadData($name);
        if ($collection->count()) {
            $this->fileRepository->saveData($name, $collection);
        }
    }

    private function filterByExclude(Collection $collection)
    {
        $excludeNames = $this->excludeNames;
        return $collection->whereNotIn('name', $excludeNames);
    }

}