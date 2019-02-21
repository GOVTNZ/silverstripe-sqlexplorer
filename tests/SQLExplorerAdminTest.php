<?php

namespace GovtNZ\SilverStripe\SqlExplorer\Tests;

use SilverStripe\Dev\FunctionalTest;

class SQLExplorerAdminTest extends FunctionalTest
{
    public function testAccessingAdmin()
    {
        $this->logInWithPermission('ADMIN');

        $editorResponse = $this->get('admin/sqlexploreradmin');
        $this->assertEquals('200', $editorResponse->getStatusCode());
    }
}
