<meta charset="utf-8">
<meta name="renderer" content="webkit">
<meta name="force-rendering" content="webkit"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- styles--}}
<link href="{{asset('static/bootstrap-material-design-dist/css/bootstrap-material-design.min.css')}}" rel="stylesheet">
<link href="{{asset('static/font-awesome-4.7.0/css/font-awesome.min.css')}}" rel="stylesheet">
<link href="{{asset('css/main.css')}}?v=20210815" rel="stylesheet">

{{-- js for jquery and bootstrap--}}
<script src="{{asset('static/jquery-3.4.1/jquery-3.4.1.min.js')}}"></script>
<script src="{{asset('static/popper.js/dist/umd/popper.min.js')}}" defer></script>
<script src="{{asset('static/bootstrap-material-design-dist/js/bootstrap-material-design.min.js')}}" defer></script>

{{-- 大文件上传 --}}
<script src="{{asset('js/uploadBig.js')}}?v=08.11" defer></script>

{{-- 提示工具 --}}
<link href="{{asset('static/notiflix/minified/notiflix-2.0.0.min.css')}}" rel="stylesheet">
<script src="{{asset('static/notiflix/minified/notiflix-2.0.0.min.js')}}" defer></script>

{{-- 开关小插件 contest/rank.blade.php; admin/settings.blade.php; --}}
<link href="{{asset('static/switch-dist/switch.css')}}" rel="stylesheet"/>
<script src="{{asset('static/switch-dist/switch.js')}}" defer></script>

{{-- table下载为表格的js插件 contest/rank.blade.php; admin/user/create.blade.php --}}
<script src="{{asset('static/jquery-table2excel/jquery.table2excel.min.js')}}" defer></script>

{{-- ckeditor5 --}}
<script src="{{asset('static/ckeditor5/build/ckeditor.js?v=20210815')}}" defer></script>
<script src="{{asset('static/ckeditor5/config.js')}}" defer></script>

{{-- 代码编辑器 codemirror  --}}
{{-- admin/problem/edit.blade.php; client/code_editor.blade.php --}}
<link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/lib/codemirror.css")}}"/>
<script src="{{asset("static/codemirror-5.61.0/lib/codemirror.js")}}" defer></script>
{{-- 主题 --}}
<link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/theme/mbo.css")}}"/>
<link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/theme/idea.css")}}"/>
{{-- 编辑器的功能 --}}
<script src="{{asset("static/codemirror-5.61.0/addon/edit/matchbrackets.js")}}" defer></script>
<script src="{{asset("static/codemirror-5.61.0/addon/edit/closebrackets.js")}}" defer></script>
<link rel="stylesheet" href="{{asset("static/codemirror-5.61.0/addon/hint/show-hint.css")}}"/>
<script src="{{asset("static/codemirror-5.61.0/addon/hint/show-hint.js")}}" defer></script>
{{-- 需要高亮的语言 --}}
<script src="{{asset("static/codemirror-5.61.0/mode/cmake/cmake.js")}}" defer></script>
<script src="{{asset("static/codemirror-5.61.0/mode/clike/clike.js")}}" defer></script>
<script src="{{asset("static/codemirror-5.61.0/mode/python/python.js")}}" defer></script>

{{-- 代码高亮 clien/code_editor.blade.php; client/solution.blade.php --}}
<link rel="stylesheet" href="{{asset("static/highlight/styles/github-gist.css")}}">
<script src="{{asset("static/highlight/highlight.pack.js")}}" defer></script>

{{-- echart画图工具 --}}
<script src="{{asset('static/echarts/echarts.min.js')}}" defer></script>

{{-- mathjax渲染latex公式; 初始会自动对elements渲染一次 --}}
<script type="text/x-mathjax-config">
    window.MathJax.Hub.Config({
        showProcessingMessages: false, //关闭js加载过程信息
        messageStyle: "none",          //不显示信息
        jax: ["input/TeX", "output/HTML-CSS"],
        tex2jax: {
            inlineMath: [["\\$", "\\$"], ["\\(", "\\)"]], //行内公式选择符
            displayMath: [["$$", "$$"], ["\\[", "\\]"]],  //段内公式选择符
            skipTags: ["script", "noscript", "style", "textarea", "pre", "code", "a", "tips"], //避开某些标签
            ignoreClass:"not_math"  // 避开class
        },
        "HTML-CSS": {
            availableFonts: ["STIX", "TeX"], //可选字体
            showMathMenu: false     //关闭右击菜单显示
        },
        elements: [document.getElementsByClassName("math_formula")]  // 允许渲染的dom
    });
    // 动态渲染语句如下
    // window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementsByClassName("math_formula")]); //math_formula是自定义类名
</script>
<script type="text/javascript" src="{{asset('static/MathJax-2.7.7/MathJax.js?config=TeX-AMS_HTML')}}" defer></script>
