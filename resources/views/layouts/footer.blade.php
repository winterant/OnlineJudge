<div id="footer" class="text-center mb-2">
    <hr>
    <div>
        <span id="localtime">{{date('Y-m-d H:i:s')}}</span>
        @if(get_setting('beian')!=null)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="http://www.beian.miit.gov.cn" target="_blank">{{get_setting('beian')}}</a>
        @endif
    </div>

    © 2020-{{date('Y')}} <a target="_blank" href="https://github.com/winterant/LDUOnlineJudge">Online Judge</a>
    by <a target="_blank" href="https://github.com/winterant">Winterant</a>.
    All Rights Reserved.
</div>

<script type="text/javascript">
    //自动更新页脚时间
    $(function () {
        let now = new Date("{{date('Y-m-d H:i:s')}}");
        setInterval(function () {
            now=new Date(now.getTime()+1000);
            var str=now.getFullYear();
            str+='-'+(now.getMonth()<9?'0':'')   +(now.getMonth()+1);
            str+='-'+(now.getDate()<10?'0':'')   +now.getDate();
            str+=' '+(now.getHours()<10?'0':'')  +now.getHours();
            str+=':'+(now.getMinutes()<10?'0':'')+now.getMinutes();
            str+=':'+(now.getSeconds()<10?'0':'')+now.getSeconds();
            document.getElementById('localtime').innerHTML=str;
        },1000); //每秒刷新时间
    })

    //通用提示框，小问号提示这是什么
    function whatisthis(text) {
        Notiflix.Report.Init({
            plainText: false, //使<br>可以换行
        });
        Notiflix.Report.Info( '{{__('sentence.Whats this')}}',text,'{{__('main.Confirm')}}');
    }
</script>
