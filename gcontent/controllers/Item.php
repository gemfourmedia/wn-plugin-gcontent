<?php namespace GemFourMedia\GContent\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Item extends Controller
{
    use \GemFourMedia\GContent\Traits\ControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',        
        'Backend\Behaviors\FormController',        
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
        'GemFourMedia.GContent.Behaviors.BatchUpdateController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $batchUpdateConfig = 'config_batchupdate.yaml';

    public $requiredPermissions = [
        'gcontent.item.manage', 
        'gcontent.item.create', 
        'gcontent.item.update', 
        'gcontent.item.delete' 
    ];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GContent', 'gcontent-main-menu', 'gcontent-menu-item');
    }
}
