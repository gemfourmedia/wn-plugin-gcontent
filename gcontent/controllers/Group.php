<?php namespace GemFourMedia\GContent\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Group extends Controller
{
    use \GemFourMedia\GContent\Traits\ControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gcontent.group.manage', 
        'gcontent.group.create', 
        'gcontent.group.update', 
        'gcontent.group.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GContent', 'gcontent-main-menu', 'gcontent-menu-group');
    }
}
