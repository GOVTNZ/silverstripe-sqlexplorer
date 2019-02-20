<?php

namespace GovtNZ\SilverStripe\SqlExplorer;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

class SQLExplorerAdmin extends ModelAdmin implements PermissionProvider
{
    private static $managed_models = [
        // 'SQLExplorerTable',
        'SQLExplorerSavedQuery'
    ];

    private static $menu_title = 'SQL Explorer';

    private static $url_segment = 'sqlexploreradmin';

    private static $extra_requirements_css = [
        'govtnz/silverstripe-sqlexplorer:client/css/sql_explorer.css'
    ];

    private static $extra_requirements_javascript = [
        'govtnz/silverstripe-sqlexplorer:client/javascript/sql_explorer.js'
    ];

    /**
     * If true, this admin requires that a user explicitly has SQL_EXPLORER permission. The purpose is to
     * have strict and explicit control. Without it, any user in the administrators group will by default
     * have access, which can be undesirable because non-technical users are frequently set up as administrators.
     *
     * @config
     */
    private static $require_explicit_permission = false;

    public function providePermissions()
    {
        return array(
            "SQL_EXPLORER" => "Provides access to SQL Explorer. Being in administration group is not sufficient."
        );
    }

    public static function set_require_explicit_permission($val)
    {
        self::$require_explicit_permission = $val;
    }

    public static function get_require_explicit_permission($val)
    {
        return self::$require_explicit_permission;
    }

    public function init()
    {
        parent::init();

        if (self::$require_explicit_permission && !Permission::check("SQL_EXPLORER")) {
            Security::permissionFailure();
        }
    }

    public function getEditForm($id = null, $fields = null)
    {
        if ($this->modelClass == 'SQLExplorerSavedQuery') {
            return $this->getQueryEditForm($id, $fields);
        }

        if ($this->modelClass == 'SQLExplorerTable') {
            return $this->getTableEditForm($id, $fields);
        }

        // should never get here, but if it does, scaffhold the default
        return parent::getEditForm($id, $fields);
    }

    // Get the edit form for saved queries. Pretty much default editor, except that we
    // add a custom ItemRequest for the ajax methods that support the detail editor.
    public function getQueryEditForm($id, $fields)
    {
        $list = $this->getList();

        $form = parent::getEditForm($id, $fields);
        $field = $form->Fields()->fieldByName('SQLExplorerSavedQuery');
        $detailEditor = $field->getConfig()->getComponentByType('GridFieldDetailForm');
        $detailEditor->setItemRequestClass('SQLExplorerQueryGrid_ItemRequest');

        return $form;
    }

    // // Get the edit form for tables.
    // // @todo Implement getTableEditForm
    public function getTableEditForm($id, $fields)
    {
        $form = parent::getEditForm($id, $fields);

        $field = $form->Fields()->fieldByName('SQLExplorerTable');
        $field->getConfig()->removeComponentsByType('GridFieldSortableHeader');

        return $form;
    }
}
