<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Control\Director;

class SQLExplorerQueryField extends TextareaField
{
    /**
     * Template uses this to determine whether to display warnings to the user
     * or not.
     */
    public function isProduction()
    {
        return Director::isLive();
    }
}
