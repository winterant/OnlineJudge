<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function list(){
        $notices=DB::table('notices')->orderByDesc('id')->paginate();
        return view('admin.notice.list',compact('notices'));
    }

    public function add(Request $request){
        if($request->isMethod('get')) {
            $pageTitle='发布公告';
            return view('admin.notice.edit',compact('pageTitle'));
        }
        if($request->isMethod('post')) {
            $notice=$request->input('notice');
            $nid=DB::table('notices')->insertGetId($notice);
            return view('admin.success',['msg'=>'成功发布公告（id='.$nid.'），你可以在首页查看']);
        }
    }

    public function update(Request $request,$id){
        if($request->isMethod('get')) {
            $pageTitle='修改公告';
            $notice=DB::table('notices')->find($id);
            return view('admin.notice.edit',compact('pageTitle','notice'));
        }
        if($request->isMethod('post')) {
            $notice=$request->input('notice');
            DB::table('notices')->where('id',$id)->update($notice);
            return view('admin.success',['msg'=>'已更新公告（id='.$id.'），你可以在首页查看']);
        }
    }

    public function delete(Request $request){
        $nids=$request->input('nids')?:[];
        DB::table('notices')->whereIn('id',$nids)->delete();
    }

    public function upload_image(Request $request){
        $image=$request->file('upload');
        $fname=uniqid(date('Ymd_His_')).'.'.$image->getClientOriginalExtension();
        $image->move(storage_path('app/public/notice/images'),$fname);
        return json_encode(['uploaded'=>true,'url'=>'/storage/notice/images/'.$fname]);
    }

    public function update_state(Request $request){
        $nids=$request->input('nids')?:[];
        $state=$request->input('state');
        return DB::table('notices')->whereIn('id',$nids)->update(['state'=>$state]);
    }
}
