<?php

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
    //保存样例文件
    $samplePath=base_path( 'storage/data/'.$problem_id.'/sample' );
    if(!is_dir($samplePath))
        mkdir($samplePath,0777,true); //最大权限，多级目录
    foreach(scandir($samplePath) as $filename){
        if($filename!=='.'&&$filename!=='..')
            unlink($samplePath.'/'.$filename); //删除原有样例文件
    }
    foreach ($samples as $k=>$txt){
        $name="sample".intval($k/2).(($k%2==0)?'.in':'.out');
        file_put_contents($samplePath.'/'.$name,$txt);
    }
}

/**
 * @param $problem_id
 * @return array  返回二维数组，第一维[sample0,sample1,...]，第二维[in,out]
 * 读取样例文件
 */
function read_problem_samples($problem_id){
    $samplePath=base_path( 'storage/data/'.$problem_id.'/sample' );
    $samples=[];
    if(is_dir($samplePath)){
        $flag=[];
        foreach(scandir($samplePath) as $filename){
            $name=pathinfo($filename,PATHINFO_FILENAME);
            $ext=pathinfo($filename,PATHINFO_EXTENSION);
            if(!isset($flag[$name]))$flag[$name]=0;
            if($ext==='in')$flag[$name]++;
            if($ext==='out')$flag[$name]++;
        }
        foreach ($flag as $key=>$val){  //in和out都有的，读取
            if($val==2){
                $in=file_get_contents($samplePath.'/'.$key.'.in');
                $out=file_get_contents($samplePath.'/'.$key.'.out');
                array_push($samples,[$in,$out]);
            }
        }
    }
    return $samples;
}




function save_problem_spj_code($problem_id, $file){
    //保存特判文件
    $spjPath=base_path( 'storage/data/'.$problem_id.'/spj' );
    if(!is_dir($spjPath))
        mkdir($spjPath,0777,true); //最大权限，多级目录
    file_put_contents($spjPath.'/spj.cpp',file_get_contents($file->getRealPath()));
    exec(sprintf('g++ -std=c++11 %s -o %s -lmysqlclient 2>&1',
        base_path('storage/data/'.$problem_id.'/spj/spj.cpp'),
        base_path('storage/data/'.$problem_id.'/spj/spj')),$output,$status);
    return $output;
}
