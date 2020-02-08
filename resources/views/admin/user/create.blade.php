@extends('layouts.admin')

@section('title','账号生成 | 后台')

@section('content')

    <h2>批量生成比赛账号</h2>

    @if(isset($users))
        <hr>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>登录名</th>
                        <th>密码</th>
                        <th>姓名</th>
                        <th>学校</th>
                        <th>班级</th>
                        <th>邮箱</th>
                        <th>资料变动次数
                            <a href="javascript:" style="color: #838383" data-toggle="tooltip"
                               title="允许用户可自行修改个人资料的次数，可防止用户随意改动。影响状态、榜单等混乱。管理员不受限制">
                                <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $item)
                        <tr>
                            <td nowrap>{{$item->username}}</td>
                            <td nowrap>{{$item->password}}</td>
                            <td nowrap>{{$item->nick}}</td>
                            <td nowrap>{{$item->school}}</td>
                            <td nowrap>{{$item->class}}</td>
                            <td nowrap>{{$item->email}}</td>
                            <td>{{$item->revise}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <hr>

    <div>
        <div class="alert alert-info">
            <p class="m-0">比赛账号分为前缀与编号。如team1024，前缀为team，编号为1024</p>
            <p class="m-0">请尽量使用没有使用过的前缀，已存在同名用户将会被删除！</p>
        </div>
        <form action="" method="post">
            @csrf

            <div class=" col-12 col-md-6 border p-2 bg-white">
                <ul class="nav nav-tabs nav-justified mb-1 border-bottom">
                    <li class="nav-item">
                        <a class="nav-link p-2 active" href="#tag_1" data-toggle="tab">前缀+编号</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link p-2" href="#tag_2" data-toggle="tab">指定用户名/学号</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="tag_1" class="tab-pane fade show active form-group">
                        <div class="form-inline">
                            <label>账号前缀：
                                <input type="text" name="data[prefix]" value="team"
                                       onkeyup="this.value=this.value.replace(/[^a-zA-Z0-9]/g,'')" class="ttt form-control">
                            </label>
                        </div>
                        <div class="form-inline">
                            <label>编号范围：
                                <input type="number" name="data[begin]" value="1" class="ttt form-control">
                                <font class="px-2">—</font>
                                <input type="number" name="data[end]" value="10" class="ttt form-control">
                            </label>
                        </div>
                    </div>
                    <div id="tag_2" class="tab-pane fade form-group w-50">
                        <label for="description">姓名/队名列表：</label>
                        <textarea id="description" name="data[stu_id]" class="ttt form-control-plaintext border bg-white"
                             rows="6" placeholder="{{"20182209134\n说明：每行一个学号"}}"></textarea>
                    </div>
                </div>
                <script type="text/javascript">
                    $(function(){
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
                              rows="6" placeholder="{{"Sparks of Fire\nSample Team Name\n说明：每行对应一个账号姓名/队伍名"}}"></textarea>
                </div>
                <div class="col-3">
                    <label for="description">学校列表：</label>
                    <textarea id="description" name="data[school]" class="form-control-plaintext border bg-white"
                        rows="6" placeholder="{{"鲁东大学 5\n烟台大学\n说明：\n校名跟空格n,则连续n个账号为该校。"}}"></textarea>
                </div>
                <div class="col-3">
                    <label for="description">班级列表：</label>
                    <textarea id="description" name="data[class]" class="form-control-plaintext border bg-white"
                        rows="6" placeholder="{{"电气1801 65\n软工1801\n说明：\n后跟空格n,则连续n个账号为该班级。"}}"></textarea>
                </div>
                <div class="col-3">
                    <label for="description">邮箱列表：</label>
                    <textarea id="description" name="data[email]" class="form-control-plaintext border bg-white"
                        rows="6" placeholder="{{"123@123.com\n456@456.com\n说明：每行对应一个邮箱"}}"></textarea>
                </div>
            </div>


            <div class="form-inline">
                <label>允许这些用户修改个人资料的次数：
                    <input type="number" name="data[revise]" value="0" required class="form-control" min="0">
                </label>
            </div>

            <div class="form-group m-4">
                <button type="submit" class="btn-lg btn-success">提交</button>
            </div>
        </form>
    </div>

@endsection
