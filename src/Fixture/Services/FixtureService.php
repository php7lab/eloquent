<?php

namespace PhpLab\Eloquent\Fixture\Services;

use Illuminate\Support\Collection;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;
use PhpLab\Eloquent\Fixture\Repositories\DbRepository;
use PhpLab\Eloquent\Fixture\Repositories\FileRepository;
use PhpLab\Eloquent\Migration\Repositories\HistoryRepository;

class FixtureService
{

    private $loadedFixtures = [];
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

    public function importAll(array $selectedTables, callable $beforeOutput = null, callable $afterOutput = null) {
        /** @var FixtureEntity[]|\Illuminate\Database\Eloquent\Collection $tableCollection */
        $tableCollection = $this->allFixtures();
        $tableCollection = EntityHelper::indexingCollection($tableCollection, 'name');

        foreach ($selectedTables as $tableName) {
            $this->importTable($tableName, $beforeOutput, $afterOutput, $tableCollection);
        }
    }

    public function importTable($tableName, callable $beforeOutput = null, callable $afterOutput = null, array $tableCollection = [])
    {
        if( array_key_exists($tableName, $this->loadedFixtures)) {
            return;
        }
        $deps = [];
        $dataFixture = $this->fileRepository->loadData($tableName);

        $this->dbRepository->truncateData($tableName);

        $deps = $dataFixture->deps();
        $data = $dataFixture->load();

        if($deps) {
            foreach ($deps as $dep) {
                $this->importTable($dep, $beforeOutput, $afterOutput, $tableCollection);
            }
        }

        if($beforeOutput) {
            $beforeOutput($tableName);
        }

        if($data) {
            $this->dbRepository->saveData($tableName, new Collection($data));
        }

        if($afterOutput) {
            $afterOutput($tableName);
        }

        $this->loadedFixtures[$tableName] = true;
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