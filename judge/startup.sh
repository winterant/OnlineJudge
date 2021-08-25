#!/bin/bash

APP_HOME=$(dirname $(dirname $(readlink -f "$0")))
cd "${APP_HOME}"/judge || exit

bash ./stop.sh
source ./config.sh

# 获取测试数据路径; 如果是相对路径，则视为当前项目的相对路径
DATA_DIR=${JG_DATA_DIR}
if [[ ${DATA_DIR:0:1} != "/" ]]; then
    DATA_DIR="${APP_HOME}/${DATA_DIR}"
fi

# 编译判题端源码
g++ ./cpp/polling.cpp -o ./polling -std=c++11 -lmysqlclient 2>&1
g++ ./cpp/judge.cpp  -o  ./judge -std=c++11 -lmysqlclient 2>&1

# 启动判题轮询进程
if [[ "$1" == "debug" ]];then
    ./polling "${DB_HOST}" "${DB_PORT}" "${DB_USERNAME}" "${DB_PASSWORD}" "${DB_DATABASE}" "${JG_MAX_RUNNING}" "${DATA_DIR}" "${JG_NAME}"
else
    ./polling "${DB_HOST}" "${DB_PORT}" "${DB_USERNAME}" "${DB_PASSWORD}" "${DB_DATABASE}" "${JG_MAX_RUNNING}" "${DATA_DIR}" "${JG_NAME}" > /dev/null &
    sleep 1;
    polling_name=$(ps -e | grep polling | awk '{print $4}')
    if [[ "${polling_name}" =~ "polling" ]];then
        echo -e "[Starting judgement process]: OK."
    else
        echo -e "[Starting judgement process]: Failed."
    fi
fi
echo -e "[Test data location] ${DATA_DIR}"
