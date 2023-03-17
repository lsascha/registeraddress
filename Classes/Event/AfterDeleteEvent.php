<?php

namespace AFM\Registeraddress\Event;

class AfterDeleteEvent
{
    protected array $records;

    protected string $tableName;

    protected bool $forceDelete;

    public function __construct(array $records, string $tableName, bool $forceDelete)
    {
        $this->records = $records;
        $this->tableName = $tableName;
        $this->forceDelete = $forceDelete;
    }

    public function getRecords()
    {
        return $this->records;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function isForceDelete(): bool
    {
        return $this->forceDelete;
    }

}
