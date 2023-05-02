<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;


/**
 * 定义一个通用的ckeditor5编辑器，使用方法示例
 * <x-ckeditor5 name="title" content="Hi!" />
 * <x-ckeditor5 name="group[name]" :content="$group->description ?? ''" />
 */
class Ckeditor5 extends Component
{
    public $title;
    public $domId; // ckditor元素id，要求唯一性
    public $name; // input元素的name属性
    public $content;
    public $preview;
    /**
     * Create a new component instance.
     */
    public function __construct($name, $content = '', $title = null, $preview = false)
    {
        $this->title = $title;
        $this->domId = uniqid('ckeditor5_');
        $this->name = $name;
        $this->content = $content;
        $this->preview = $preview == 1 || $preview == 'true' ? true : false; // 默认不预览
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ckeditor5');
    }
}
