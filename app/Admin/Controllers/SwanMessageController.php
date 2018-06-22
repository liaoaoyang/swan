<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SwanMessageModel;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class SwanMessageController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('SWAN 消息');
            $content->description('已下发消息');

            $grid = $this->grid();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->perPages([5, 10]);
            $grid->model()->orderBy('created_at', 'desc');

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
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
        $class = get_class(SwanMessageModel::createModel());

        return Admin::grid($class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->column('openid');
            $grid->column('text', '标题');
            $grid->column('desp', '正文');

            $grid->created_at();
            $grid->updated_at();
        });
    }
}