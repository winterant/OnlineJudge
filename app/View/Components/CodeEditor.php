<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;


/**
 * 定义一个通用的code-editor编辑器，使用方法示例
 * <x-code-editor name="code" code="Hi!" />
 * <x-code-editor name="problem[spj_code]" :code="$code ?? ''" />
 */
class CodeEditor extends Component
{
    public $title;
    public $domId; // 元素id，要求唯一性
    public $languages; // 编程语言列表[$language_id => $language_name]
    public $lang_name; // input 语言的name属性
    public $code_name; // textarea 代码的name属性
    public $code; // 初始代码
    public $lang; // 初始语言

    public function __construct($langName, $codeName, $lang = 0, $code = '', array $languages = null, $bitlanguages = null, $title = null)
    {
        $this->title = $title;
        $this->domId = uniqid('code_editor_');
        $this->lang_name = $langName;
        $this->code_name = $codeName;
        $this->code = $code;
        $this->lang = $lang;
        // 指定语言列表
        $this->languages = $languages ?? config('judge.lang');
        // 二进制位选取语言
        if ($bitlanguages != null) {
            $temp = [];
            foreach ($this->languages as $k => $lang) {
                if (($bitlanguages >> $k) & 1)
                    $temp[$k] = $lang;
            }
            $this->languages = $temp;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.code-editor');
    }
}
