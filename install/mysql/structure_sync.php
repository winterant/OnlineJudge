<?php
if($argc<3)return;

$db = [
    'host'  => 'localhost',
    'user'  => $argv[1],
    'pwd'   => $argv[2],
    'db1'   => 'lduoj_upgrade', //源表
    'db2'   => 'lduoj', //目标表，即要改动的表
];

$result_sql="/* Modify database {$db['db2']} with reference to {$db['db1']}. */\n\n";  //字符串用于拼接修改语句sql
$result_sql.="SET NAMES utf8mb4;\n";  //设置字符集
$result_sql.="SET FOREIGN_KEY_CHECKS = 0;\n\n";  //取消外键检查

//建立连接
$conn = mysqli_connect($db['host'], $db['user'], $db['pwd']) or die(mysqli_error());

//读取表结构，转为数组形式
$stru1 = "SELECT TABLE_SCHEMA,TABLE_NAME,COLUMN_NAME,ORDINAL_POSITION,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_TYPE,COLUMN_COMMENT,EXTRA,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '{$db['db1']}'";
$stru2 = "SELECT TABLE_SCHEMA,TABLE_NAME,COLUMN_NAME,ORDINAL_POSITION,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_TYPE,COLUMN_COMMENT,EXTRA,DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '{$db['db2']}'";
$info1 = query_as_array($conn, $stru1);
$info2 = query_as_array($conn, $stru2);

$data1 = transform2($info1);
$data2 = transform2($info2);

$key1 = array_keys($data1); //db1的表
$key2 = array_keys($data2); //db2的表

$diff1 = array_diff($key1,$key2);  //得出db2缺少的表
$diff2 = array_diff($key2,$key1);  //得出db2多余的表

//在db2中创建db1有但db2没有的表
if(!empty($diff1))
{
    foreach($diff1 as $val)
    {
        $create_sql = "show create table {$db['db1']}.{$val}";
        foreach(query_as_array($conn, $create_sql) as $sql)
        {
            $result_sql.=$sql['Create Table'].";\n\n";
        }
    }
}

//删除db2中多余的表。注意，重命名的表会被删除！！
if(!empty($diff2))
{
    foreach($diff2 as $val)
    {
        $result_sql.="drop table {$val};\n";
    }
    $result_sql.="\n";
}

//比较字段信息
foreach($data1 as $key1 => $val1)
{
    foreach($data2 as $key2 => $val2)
    {
        if($key1 == $key2) //同一个表
        {
            $column1 = array_diff_key($val1,$val2); //db1比db2多的字段
            $column2 = array_diff_key($val2,$val1); //db2比db1多的字段

            //在db2中新增比db1缺少的字段
            if(!empty($column1))
            {
                foreach($column1 as $col_info)
                {
                    $alter_sql = "alter table {$col_info['TABLE_NAME']} add column {$col_info['COLUMN_NAME']} {$col_info['COLUMN_TYPE']}";
                    if($col_info['IS_NULLABLE'] = 'NO')
                    {
                        $alter_sql .= " not null ";
                    }
                    if($col_info['COLUMN_DEFAULT'] !== null)
                    {
                        if($col_info['DATA_TYPE'] == 'bit' || $col_info['COLUMN_DEFAULT']=='CURRENT_TIMESTAMP')
                        {
                            $alter_sql .= " default ".$col_info['COLUMN_DEFAULT'];
                        }
                        else
                        {
                            $alter_sql .= " default '{$col_info['COLUMN_DEFAULT']}'";
                        }
                    }
                    if($col_info['EXTRA'])
                    {
                        $alter_sql .= " ".$col_info['EXTRA'];
                    }
                    if($col_info['COLUMN_COMMENT'])
                    {
                        $alter_sql .= " comment '{$col_info['COLUMN_COMMENT']}'";
                    }
                    $result_sql.=$alter_sql.";\n";
                }
            }

            //在db2中删除比db1多余的字段。注意，重命名的字段会被删除！！
            if(!empty($column2))
            {
                foreach($column2 as $col_info)
                {
                    $result_sql .= "alter table {$col_info['TABLE_NAME']} drop column {$col_info['COLUMN_NAME']};\n";
                }
            }

            //在db2中更新字段的属性，使其与db1同步
            foreach($val1 as $col1 => $info1)
            {
                foreach($val2 as $col2 => $info2)
                {
                    if ($col1 == $col2)
                    {
                        if($info1['ORDINAL_POSITION'] !== $info2['ORDINAL_POSITION'] || $info1['COLUMN_DEFAULT'] !== $info2['COLUMN_DEFAULT'] || $info1['IS_NULLABLE'] !== $info2['IS_NULLABLE'] || $info1['COLUMN_TYPE'] !== $info2['COLUMN_TYPE'] || $info1['COLUMN_COMMENT'] !== $info2['COLUMN_COMMENT'])
                        {
                            $modify = "alter table {$info2['TABLE_NAME']} modify column {$info1['COLUMN_NAME']} {$info1['COLUMN_TYPE']}";
                            if($info1['IS_NULLABLE'] == 'NO')
                            {
                                $modify .= " not null ";
                            }
                            if($info1['COLUMN_DEFAULT'] !== null)
                            {
                                if($info1['DATA_TYPE'] == 'bit' || $col_info['COLUMN_DEFAULT']=='CURRENT_TIMESTAMP')
                                {
                                    $modify .= " default ".$info1['COLUMN_DEFAULT'];
                                }
                                else
                                {
                                    $modify .= " default '{$info1['COLUMN_DEFAULT']}'";
                                }
                            }
                            if($info1['EXTRA'])
                            {
                                $modify .= " ".$info1['EXTRA'];
                            }
                            if($info1['COLUMN_COMMENT'])
                            {
                                $modify .= " comment '{$info1['COLUMN_COMMENT']}'";
                            }
                            if($info1['ORDINAL_POSITION'] == 1)
                            {
                                $modify .= " first";
                            }
                            else
                            {
                                $last_pos = $info1['ORDINAL_POSITION'] - 1;
                                $last_col = query_as_array($conn,"select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where ORDINAL_POSITION = {$last_pos} and table_schema = '{$db['db1']}' and table_name = '{$info1['TABLE_NAME']}'");
                                $modify .= " after ".$last_col[0]['COLUMN_NAME'];
                            }
                            $result_sql.=$modify.";\n";
                        }
                    }
                }
            }
        }

    }
}
$result_sql.="\nSET FOREIGN_KEY_CHECKS = 1;\n\n";  //恢复外键检查
echo $result_sql;

//sql查询，并返回数组形式
function query_as_array($conn,$sql)
{
    mysqli_query($conn,"set names 'utf8mb4'") or die(mysqli_error($conn));
    $res = mysqli_query($conn,$sql) or die(mysqli_error($conn));
    $array = array();
    if($res)
    {
        while ( $row = mysqli_fetch_assoc($res))
        {
            $array[] = $row;
        }
        return $array;
    }
    else
    {
        return $res;
    }
}

//将sql查询到的表结构数组转为二维数组
function transform2($array)
{
    $data = array();
    foreach($array as $key => $item)
    {
        if(!array_key_exists($item['TABLE_NAME'], $data))
        {
            foreach ($array as $value)
            {
                if ($value['TABLE_NAME'] == $item['TABLE_NAME'])
                {
                    $data[$item['TABLE_NAME']][$value['COLUMN_NAME']] = $value;
                }
            }
        }
    }
    return $data;
}
?>
