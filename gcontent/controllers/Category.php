<?php namespace GemFourMedia\GContent\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Category extends Controller
{
    use \GemFourMedia\GContent\Traits\ControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',        
        'Backend\Behaviors\FormController',        
        'Backend\Behaviors\ReorderController',
        'GemFourMedia.GContent.Behaviors.BatchUpdateController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $batchUpdateConfig = 'config_batchupdate.yaml';

    public $requiredPermissions = [
        'gcontent.category.manage', 
        'gcontent.category.create', 
        'gcontent.category.update', 
        'gcontent.category.delete' 
    ];
    
    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GContent', 'gcontent-main-menu', 'gcontent-menu-category');
    }
}
