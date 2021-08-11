<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /*
     * 保存request传过来的一块文件，指定保存位置与文件名
     */
    public function upload(Request $request,$save_dir,$save_name){
        $temp_save_dir='upload_big_temp/'.(Auth::check()?Auth::id():'guest');
        if(!Storage::exists($temp_save_dir)){  //临时文件夹
            Storage::makeDirectory($temp_save_dir);
        }

        $block=$request->file('block');
        $block_id=intval( $request->input('block_id') );  //0~tot-1
        $block_tot=intval( $request->input('block_tot') );

        $block->move(storage_path('app/'.$temp_save_dir),$block_id); //以块号为名保存当前块
        if($block_id == $block_tot-1){  //整个文件上传完成
            if(!Storage::exists($save_dir)){  //保存文件夹
                Storage::makeDirectory($save_dir);
            }
            for($i=0;$i<$block_tot;$i++){
                $content=Storage::get($temp_save_dir.'/'.$i);
                file_put_contents(storage_path('app/'.$save_dir.'/'.$save_name),$content,$i?FILE_APPEND:FILE_TEXT);//追加:覆盖
            }
            Storage::deleteDirectory($temp_save_dir); //删除临时文件
            return true;  //标记上传完成
        }
        return false;
    }

    /*
     * CKeditor 上传图片
     */
    public function ck_upload_image(Request $request){
        $image=$request->file('upload'); //ckeditor传过来的文件名upload
        $fdir='public/ckeditor/images/'.date('Ymd');  //保存文件夹
        $fname=uniqid(date('His_')).'.'.$image->getClientOriginalExtension();//保存名
        $image->move(storage_path('app/'.$fdir),$fname);
        return json_encode(['uploaded'=>true,'url'=> Storage::url($fdir.'/'.$fname)]);
    }
}
