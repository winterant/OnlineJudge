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
 * @param array $arr
 * @param string $configName 相对于/config/
 * @function 将数组以php格式写入配置文件
 */
function arrayToConfig(array $arr,string $configName){
    $text="<?php\n\n"."return"." [\n";
    foreach ($arr as $key=>$value){
        if(is_numeric($key))
            $text.="\t".$key."\t=> ";
        else //is string
            $text.="\t\"".$key."\"\t=> ";

        if (is_string($value))
            $text.="\"".$value."\",\n";
        else if(is_numeric($value))
            $text.=$value.",\n";
        else if(is_bool($value))
            $text.=($value?"true":"false").",\n";
    }
    $text.="];";
    file_put_contents(base_path('config/'.$configName),$text);
}


/**
 * @param $problem_id
 * @param $samples： 一维数组  偶数项.in <=> 奇数项.out
 * 保存样例到文件
 */
function save_problem_samples($problem_id, $samples){
    $dir='data/'.$problem_id.'/sample';
    Storage::delete($dir);  //删除原有样例文件
    foreach ($samples as $k=>$txt){
        $name="sample".intval($k/2).(($k%2==0)?'.in':'.out');
        Storage::put($dir.'/'.$name,$txt);
    }
}

/**
 * @param $problem_id
 * @return array  返回二维数组，第一维[sample0,sample1,...]，第二维[in,out]
 * 读取样例文件
 */
function read_problem_samples($problem_id){
    $dir='data/'.$problem_id.'/sample';
    $samples=[];
    foreach (Storage::allFiles($dir) as $filepath){
        $name=pathinfo($filepath,PATHINFO_FILENAME);  //文件名
        $ext=pathinfo($filepath,PATHINFO_EXTENSION);    //拓展名
        if(!isset($flag[$name]))$flag[$name]=0;
        if($ext==='in')$flag[$name]++;
        if($ext==='out')$flag[$name]++;
    }
    foreach (isset($flag)?$flag:[] as $key=>$val){  //in和out都有的，读取
        if($val==2){
            $in =Storage::get($dir.'/'.$key.'.in');
            $out=Storage::get($dir.'/'.$key.'.out');
            array_push($samples,[$in,$out]);
        }
    }
    return $samples;
}


/**
 * @param $problem_id
 * @param $file
 * @return mixed
 *  保存特判文件
 */
function save_problem_spj($problem_id, $file){
    $dir='data/'.$problem_id.'/spj';
    Storage::delete($dir);
    $file->move(storage_path('app/'.$dir),'spj.cpp');
//    chmod($spjPath.'/spj.cpp',0777);
//    exec(sprintf('sudo g++ -std=c++11 %s -o %s -lmysqlclient 2>&1',$spjPath.'/spj.cpp',$spjPath.'/spj'),$output);
//    return $output;
}
