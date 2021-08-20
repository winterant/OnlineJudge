#!/bin/bash

APP_HOME=$(dirname $(dirname $(readlink -f "$0")))
cd "${APP_HOME}"/judge || exit

bash ./stop.sh
source "${APP_HOME}"/.env

# 获取测试数据路径; 如果是相对路径，则视为当前项目的相对路径
DATA_DIR=${JG_DATA_DIR}
if [[ ${DATA_DIR:0:1} != "/" ]]; then
    DATA_DIR="${APP_HOME}/${DATA_DIR}"
fi
echo "[Test data location] ${DATA_DIR}"

if [ ! -d ./program ]; then
    mkdir ./program
fi

g++ ./cpp/polling.cpp -o ./program/polling -std=c++11 -lmysqlclient 2>&1
g++ ./cpp/judge.cpp  -o  ./program/judge -std=c++11 -lmysqlclient 2>&1

cd ./program || exit

if [[ "$1" == "debug" ]];then
    ./polling "${DB_HOST}" "${DB_PORT}" "${DB_USERNAME}" "${DB_PASSWORD}" "${DB_DATABASE}" "${JG_MAX_RUNNING}" "${DATA_DIR}" "${JG_NAME}"
else
    ./polling "${DB_HOST}" "${DB_PORT}" "${DB_USERNAME}" "${DB_PASSWORD}" "${DB_DATABASE}" "${JG_MAX_RUNNING}" "${DATA_DIR}" "${JG_NAME}" > /dev/null &
    sleep 1;
    polling_name=$(ps -e | grep polling | awk '{print $4}')
    if [ "${polling_name}" == "polling" ];then
        echo "[Starting judging processes]: OK."
    else
        echo "[Starting judging processes]: Failed."
    fi
fi
