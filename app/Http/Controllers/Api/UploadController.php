<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // CKeditor 上传图片
    public function ck_upload_image(Request $request){
        $image=$request->file('upload'); //ckeditor传过来的文件名upload
        $allowed_ext = ["jfif", "pjpeg", "jpeg", "pjp", "jpg", "png", ".gif", "bmp", "dib", "webp", "tiff", "tif"];
        if(in_array($image->getClientOriginalExtension(), $allowed_ext)){
            $fdir='public/ckeditor/images';  //保存文件夹
            $fname=uniqid(date('YmdHis_')).'.'.$image->getClientOriginalExtension();//保存名
            $image->move(storage_path('app/'.$fdir),$fname);
            return json_encode(['uploaded'=>true,'url'=> Storage::url($fdir.'/'.$fname)]);
        }
        //图片格式有误，不允许上传，杜绝上传有害脚本的可能
        return json_encode(['uploaded'=>false,'url'=> null]);
    }
}
