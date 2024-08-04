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
    public $htmlPropNameOfLang; // input 语言的name属性
    public $htmlPropNameOfCode; // textarea 代码的name属性
    public $code; // 初始代码
    public $lang; // 初始语言
    // 当 $useLocalStorage == true 时，需要提供$problemId（必须）、$contestId（可选）作为缓存代码的索引
    public $useLocalStorage; // 是否让浏览器缓存已选中语言、已编辑代码
    public $contestId;
    public $problemId;
    public bool $banCodeEditor;

    public function __construct($htmlPropNameOfLang, $htmlPropNameOfCode, $lang = null, $code = '', array $languages = null, $bitlanguages = null, $title = null, $useLocalStorage = true, $problemId = null, $contestId = null, $banCodeEditor = false)
    {
        $this->title = $title;
        $this->domId = uniqid('code_editor_');
        $this->htmlPropNameOfLang = $htmlPropNameOfLang;
        $this->htmlPropNameOfCode = $htmlPropNameOfCode;
        $this->code = $code;
        $this->lang = $lang;
        $this->useLocalStorage = $useLocalStorage ?? false; // 默认不使用缓存
        $this->contestId = $contestId;
        $this->problemId = $problemId;
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
        $this->banCodeEditor = $banCodeEditor;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.code-editor');
    }
}
