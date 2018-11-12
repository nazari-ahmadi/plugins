<?php namespace Rantiz\Gateway\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Lang;
use Rantiz\Gateway\Models\Transaction;
use Carbon\Carbon;

class Lists extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['ls.details.journal.access_transaction'];

    public $bodyClass = 'compact-container';
    

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rantiz.Gateway', 'gateway','lists');
    }

    public function index_onDelete()
    {
        date_default_timezone_set('Asia/Tehran');
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $checkId) {
                if ((!$transaction = Transaction::find($checkId))) {
                    continue;
                }
                $transaction->deleted = 1;
                $transaction->deleted_at = Carbon::now();              
                $transaction->save();
            }

            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }

        return $this->listRefresh();
    }

    public function index_onUnDelete()
    {
        date_default_timezone_set('Asia/Tehran');
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $checkId) {
                if ((!$transaction = Transaction::find($checkId))) {
                    continue;
                }
                $transaction->deleted = 0;
                $transaction->deleted_at = NULL;              
                $transaction->save();
            }

            Flash::success(Lang::get('rantiz.gateway::lang.form.view.un_delete_selected_success'));
        }

        return $this->listRefresh();
    }


    /**
     * {@inheritDoc}
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if ($record->deleted) {
            return 'safe disabled';
        }
    }

}