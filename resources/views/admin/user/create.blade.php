@extends('layouts.admin')

@section('title','账号生成 | 后台')

@section('content')


    <h2>批量生成比赛账号</h2>
    <hr>
    @if(isset($users))
        <p class="alert alert-success">已成功生成{{count($users)}}个账号，你可以1.下载表格，2.点击复制,将账号与密码复制到剪切板。</p>
        <a class="btn" onclick="down_users()"><i class="fa fa-cloud-download" aria-hidden="true"></i>下载文档</a>
        <a class="btn" onclick="copy('copy_users')" title="复制登录名+密码"><i class="fa fa-copy" aria-hidden="true"></i>复制</a>
        <div class="table-responsive">
                <table id="table2excel" class="table table-striped table-hover table-sm">
                    <thead>
                    <tr>
                        <th nowrap>登录名</th>
                        <th nowrap>密码</th>
                        <th nowrap>姓名/队名</th>
                        <th nowrap>学校</th>
                        <th nowrap>班级</th>
                        <th nowrap>邮箱</th>
                        <th nowrap>资料变动次数</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $item)
                        <tr>
                            <td nowrap>{{$item['username']}}</td>
                            <td nowrap>{{$item['password']}}</td>
                            <td nowrap>{{$item['nick']}}</td>
                            <td nowrap>{{$item['school']}}</td>
                            <td nowrap>{{$item['class']}}</td>
                            <td nowrap>{{$item['email']}}</td>
                            <td>{{$item['revise']}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        <textarea id="copy_users" hidden>@foreach($users as $u){{$u['username']}}&#9;{{$u['password']}}&#13;@endforeach</textarea>
        <script>
            function down_users(){
                $("#table2excel").table2excel({
                    name: "users",
                    // Excel文件的名称
                    filename: "批量账号"
                });
            }
            function copy(tag_id) {
                $("body").append('<textarea id="copy_temp">'+$('#'+tag_id).html()+'</textarea>');
                $("#copy_temp").select();
                document.execCommand("Copy");
                $("#copy_temp").remove();
                Notiflix.Notify.Success('已复制到本地剪切板');
            }
        </script>
    @else
        <div>
            @if(session('exist_users'))
                <div class="alert alert-danger">
                    <p class="m-0">生成失败！</p>
                    <p class="m-0">对于您本次要生成的账号，系统检测到以下用户名已存在，您有两种解决方法：</p>
                    <p class="m-0">(1)：更改要创建的用户名，不再与已存在用户冲突</p>
                    <p>(2)：取消本页最后的“检查重名用户”再提交，此方式将覆盖已存在的重名用户</p>
                    <p class="m-0">
                        重名用户：
                        @foreach(session('exist_users') as $item)
                            <a href="{{route('user',$item)}}" target="_blank">{{$item}}</a>
                        @endforeach
                    </p>
                </div>
            @endif
            <form action="" method="post">
                @csrf

                <div class=" col-12 col-md-6 border p-2 bg-white">
                    <ul class="nav nav-tabs nav-justified mb-1 border-bottom">
                        <li class="nav-item">
                            <a class="nav-link p-2 active" href="#tag_1" data-toggle="tab">方式1.前缀+编号</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-2" href="#tag_2" data-toggle="tab">方式2.指定用户名/学号</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="tag_1" class="tab-pane fade show active form-group">
                            <div class="form-inline">
                                <label>账号前缀：
                                    <input type="text" name="data[prefix]" value="{{old('data.prefix')?:'team'}}"
                                           onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9]/g,'')" class="ttt form-control">
                                </label>
                            </div>
                            <div class="form-inline">
                                <label>编号范围：
                                    <input type="number" name="data[begin]" value="{{old('data.begin')?:1}}" class="ttt form-control">
                                    <font class="px-2">—</font>
                                    <input type="number" name="data[end]" value="{{old('data.end')?:10}}" class="ttt form-control">
                                </label>
                            </div>
                        </div>
                        <div id="tag_2" class="tab-pane fade form-group w-50">
                            <label for="description">用户名/学号列表：</label>
                            <textarea id="description" name="data[stu_id]" class="ttt form-control-plaintext border bg-white"
                                 rows="6" placeholder="{{"20182209134\n说明：每行一个学号；仅允许英文字母或数字！"}}">{{old('data.stu_id')?:null}}</textarea>
                        </div>
                    </div>
                    <script type="text/javascript">
                        $(function(){
                            if($("#description").val()!=''){
                                $("a[href='#tag_2']").click();
                            }
                            {{-- 监听code/file的选项卡，选中时为输入框添加required属性 --}}
                            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                                var activeTab = $(e.target).attr('href'); // 获取已激活的标签页
                                var previousTab = $(e.relatedTarget).attr('href');// 获取上一个标签页
                                $(activeTab+' .ttt').attr('required',true);
                                $(previousTab+' .ttt').attr('required',false);
                                if(activeTab==='#tag_1')
                                    $(previousTab+' .ttt').val('');//清空学号，学号为空代表使用前缀+编号方式
                            });
                        });
                    </script>
                </div>



                <div class="form-group row my-5">
                    <div class="col-3">
                        <label for="description">姓名/队名列表：</label>
                        <textarea id="description" name="data[nick]" class="form-control-plaintext border bg-white"
                                  rows="6" placeholder="{{"Sparks of Fire\nSample Team Name\n说明：每行对应一个账号姓名/队伍名"}}">{{old('data.nick')?:null}}</textarea>
                    </div>
                    <div class="col-3">
                        <label for="description">学校列表：</label>
                        <textarea id="description" name="data[school]" class="form-control-plaintext border bg-white"
                            rows="6" placeholder="{{"鲁东大学 5\n烟台大学\n说明：\n校名跟空格n,则连续n个账号为该校。"}}">{{old('data.school')?:null}}</textarea>
                    </div>
                    <div class="col-3">
                        <label for="description">班级列表：</label>
                        <textarea id="description" name="data[class]" class="form-control-plaintext border bg-white"
                            rows="6" placeholder="{{"电气1801 65\n软工1801\n说明：\n后跟空格n,则连续n个账号为该班级。"}}">{{old('data.class')?:null}}</textarea>
                    </div>
                    <div class="col-3">
                        <label for="description">邮箱列表：</label>
                        <textarea id="description" name="data[email]" class="form-control-plaintext border bg-white"
                            rows="6" placeholder="{{"123@123.com\n456@456.com\n说明：每行对应一个邮箱"}}">{{old('data.email')?:null}}</textarea>
                    </div>
                </div>


                <div class="form-inline">
                    <label>允许这些用户修改个人资料的次数：
                        <input type="number" name="data[revise]" value="{{old('data.revise')?:0}}" required class="form-control" min="0">
                    </label>
                </div>

                <div class="custom-control custom-checkbox m-2">
                    <input type="checkbox" name="data[check_exist]" checked class="custom-control-input" id="customCheck">
                    <label class="custom-control-label pt-1" for="customCheck">
                        检查重名用户； 若您不勾选此项，当生成的账号已存在时，将直接覆盖已存在用户
                    </label>
                </div>

                <div class="form-group m-4">
                    <button type="submit" class="btn-lg btn-success">提交</button>
                </div>
            </form>
        </div>
    @endif
@endsection
