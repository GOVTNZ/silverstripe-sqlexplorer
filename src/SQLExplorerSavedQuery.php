<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\ORM\DataObject;

class SQLExplorerSavedQuery extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'SQLText' => 'Text'
    );

    private static $singular_name = "Saved Query";

    private static $table_name = 'SavedQuery';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('SQLText');
        $fields->addFieldsToTab('Root.Main', array(
            new SQLExplorerQueryField('SQLText', 'SQL query'),
            new LiteralField('ResultData', '<div class="sql-explorer result-data"></div>'),
            new LiteralField('QueryError', '<div class="sql-explorer query-error"></div>')
        ));

        return $fields;
    }
}
