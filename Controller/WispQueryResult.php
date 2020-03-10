<?php

Class WispQueryResult
{
    protected $PdoStatement;
    Protected $isRecordAvailable;
    protected $currentRow;
    protected $records;
    protected $currentRecordIndex;

    function __construct($ParamPdoStatement, $ParamIsRecordAvailable)
    {
        $this->records = array();
        $this->PdoStatement = $ParamPdoStatement;
        $this->isRecordAvailable = $ParamIsRecordAvailable;
        $this->OrganizeRecords();
        $this->Next();
    }

    // ...
    protected function OrganizeRecords()
    {
        $i = 0;
        while ($row = $this->PdoStatement->fetch(PDO::FETCH_ASSOC)) {

            if (!array_key_exists('TABLE_CATALOG', $row)) {
                $this->records[$i] = $row;
                $i++;
            }

        }

        $this->currentRecordIndex = 0;
    }

    // OLD / DEPRECATED
    function Next()
    {
        $this->currentRow = $this->PdoStatement->fetchAll();
    }

    // ...
    function NextRecord()
    {
        if ($this->currentRecordIndex < $this->GetRecordCount() - 1) {
            $this->currentRecordIndex++;
            return true;
        }

        return false;
    }

    // ...

    function GetRecordCount() : int
    {
        return $this->PdoStatement->rowCount();
    }

    // ...

    function IsRecordAvailable()
    {
        return $this->isRecordAvailable;
    }

    // Get column ID from column Name
    public function ColumnToId(string $ParamColumnName)
    {
        $c = $this->PdoStatement->columnCount();

        if ($c == 0) {
            echo 'Column count = 0 !';
            exit();
        }

        for ($i = 0; $i < $c; $i++) {
            if ($this->PdoStatement->getColumnMeta($i)["name"] == $ParamColumnName) {
                return $i;
            }
        }
    }

    // ...
    public function GetColumnValue(string $ParamColumnName)
    {
        return $this->records[$this->currentRecordIndex][$ParamColumnName];
    }

    // ...
    public function GetAllRecords()
    {
        return $this->records;
    }
}
