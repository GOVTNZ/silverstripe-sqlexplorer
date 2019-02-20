<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Search\SearchContext;

class SQLExplorerTableSearchContext extends SearchContext
{
    public function getResults($searchParams, $sort = false, $limit = false)
    {
        $searchParams = array_filter((array)$searchParams, array($this,'clearEmptySearchFields'));

        $tables = DB::tableList();
        $result = new ArrayList();

        $i = 1;

        foreach ($tables as $k => $tableName) {
            $table = new SQLExplorerTable();
            $table->ID = $i++;
            $table->TableName = $tableName;
            $table->Title = $tableName;  // necessary to get grid field to display the name on the detail editor.
            $result->push($table);
        }

        return $result;
    }
}
