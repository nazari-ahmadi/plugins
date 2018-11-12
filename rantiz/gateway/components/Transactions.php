<?php namespace Rantiz\Gateway\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Gateway;
use Lang;
use Auth;
use Rantiz\Gateway\Models\Transaction;
use Larabookir\Gateway\Enum;
use Carbon\Carbon;
use Flash;

class Transactions extends ComponentBase
{
    /**
     * @var RainLab\Blog\Models\Post The post model used for display.
     */
    public $post;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    public function componentDetails()
    {
        return [
            'name'        => 'rantiz.gateway::lang.components.transactions',
            'description' => 'rantiz.gateway::lang.components.transactions_desc'
        ];
    }

    public function defineProperties()
    {
        return [

        ];
    }

    public function onRun()
    {
        $userID = Auth::getUser()->id;
        $this->page['lists'] = Transaction::whereUserId($userID)->whereStatus(Enum::TRANSACTION_SUCCEED)->whereDeleted(0)->get();
    }

    public function onDelete()
    {
        date_default_timezone_set('Asia/Tehran');
        $post = post();
        
        if ($transaction = Transaction::find($post['id'])) {
            $transaction->deleted = 1;
            $transaction->deleted_at = Carbon::now();              
            $transaction->save();
        }
    }
}
