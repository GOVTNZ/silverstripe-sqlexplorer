<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Control\Director;

class SQLExplorerSavedQuery extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'SQLText' => 'Text'
    ];

    private static $singular_name = "Saved Query";

    private static $table_name = 'SavedQuery';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('SQLText');
        $fields->addFieldsToTab('Root.Main', [
            new SQLExplorerQueryField('SQLText', 'SQL query'),
            new LiteralField('ResultData', '<div class="sql-explorer result-data"></div>'),
            new LiteralField('QueryError', '<div class="sql-explorer query-error"></div>'),
            LiteralField::create('Environment', sprintf(
                '<div class="alert alert-info" style="margin-top: 10px">Environment: <strong>%s</strong></div>',
                Director::get_environment_type()
            ))
        ]);

        return $fields;
    }
}
