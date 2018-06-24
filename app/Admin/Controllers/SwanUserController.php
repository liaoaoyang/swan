<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SwanKeyOpenidMapModel;
use App\Swan;
use Carbon\Carbon;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use EasyWeChat\Foundation\Application as WeChatApplication;
use Cache;

class SwanUserController extends Controller
{
    use ModelForm;
    use SwanColumns;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('SWAN 用户');
            $content->description('用户基本信息');

            $grid = $this->grid();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->perPages([5, 10]);

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->filter(function($filter){
                $filter->disableIdFilter();
                $filter->equal('key', 'key');
            });

            $content->body($grid);
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $modelClass = SwanKeyOpenidMapModel::createModel();
        $idName = Swan::getModelIdFieldName($modelClass);
        $class = get_class($modelClass);
        $weChatApp = new WeChatApplication(Swan::loadEasyWeChatConfig());

        return Admin::grid($class, function (Grid $grid) use ($weChatApp, $idName) {
            $grid->column($idName, 'ID')->sortable();
            $grid->column('openid');
            $grid->column('key');
            $this->addColumnWeChatNickname($grid, $weChatApp);
            $grid->created_at();
            $grid->updated_at();

            $grid->column('status','用户状态')->display(function () {
                switch ($this->status) {
                    case SwanKeyOpenidMapModel::STATUS_DISABLED:
                        return '已冻结';

                    case SwanKeyOpenidMapModel::STATUS_ENABLED:
                        return '正常';

                    case SwanKeyOpenidMapModel::STATUS_DISABLED_BY_USER:
                        return '用户关闭推送';

                    default:
                        return "未知:{$this->status}";
                }
            });
        });
    }
}
