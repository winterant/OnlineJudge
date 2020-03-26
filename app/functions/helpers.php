<?php

use Illuminate\Support\Facades\Storage;


/**
 * function: 修改项目.env文件
 * args: array(key=>aimValue);
 */
function modifyEnv(array $data)
{
    $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';
    $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));
    $contentArray->transform(function ($item) use ($data){
        foreach ($data as $key => $value){
            if(strpos(explode('=',$item)[0],$key)!==false){
                return $key.'='.$value;  //modify
            }
        }
        return $item;
    });
    $content = implode($contentArray->toArray(), "\n");
    file_put_contents($envPath,$content,LOCK_EX);
}


/**
 * @param $problem_id
 * @return array  返回二维数组，第一维[sample0,sample1,...]，第二维[in,out]
 * 读取样例文件
 */
function read_problem_samples($problem_id){
    $dir='data/'.$problem_id.'/sample';
    foreach (Storage::allFiles($dir) as $filepath){
        $name=pathinfo($filepath,PATHINFO_FILENAME);  //文件名
        $ext=pathinfo($filepath,PATHINFO_EXTENSION);    //拓展名
        if(!isset($flag[$name]))$flag[$name]=0;
        if($ext==='in')$flag[$name]++;
        if($ext==='out')$flag[$name]++;
    }
    $samples=[];
    foreach (isset($flag)?$flag:[] as $key=>$val){  //in和out都有的，读取
        if($val==2){
            $in =Storage::get($dir.'/'.$key.'.in');
            $out=Storage::get($dir.'/'.$key.'.out');
            $samples[]=[$in,$out];
        }
    }
    return $samples;
}

/**
 * @param $problem_id
 * @param $ins,$outs，均为文本
 * 保存样例到文件
 */
function save_problem_samples($problem_id, $ins,$outs){
    Storage::deleteDirectory(sprintf('data/%d/sample',$problem_id));  //删除原有样例文件
    foreach ($ins as $i=>$in)Storage::put(sprintf('data/%d/sample/sample%d.in',$problem_id,$i),$in);
    foreach ($outs as $i=>$out)Storage::put(sprintf('data/%d/sample/sample%d.out',$problem_id,$i),$out);
}

/**
 * @param $problem_id
 * @param $code
 * @return mixed
 *  保存特判文件
 */
function save_problem_spj($problem_id, $code){
    Storage::put(sprintf('data/%d/spj/spj.cpp',$problem_id),$code);
    $dir=storage_path('app/data/'.$problem_id.'/spj');
    exec("sudo g++ ".$dir."/spj.cpp -o ".$dir."/spj -lm -std=c++11 2>&1", $out);
    return implode('<br>',$out);
}


//将一个数字题号（从1开始）转为大写字母
function index2ch(int $index){
    if($index<=26)
        return chr($index+65-1);
    return $index;
}
