#!/bin/bash

bash ./stop.sh

cd `dirname $0`
source ../.env

if [ ! -d ./program ]; then
  mkdir ./program
fi

g++ ./cpp/polling.cpp -o ./program/polling -std=c++11 -lmysqlclient
g++ ./cpp/judge.cpp  -o  ./program/judge -std=c++11 -lmysqlclient

cd ./program
if [ "$1" == "debug" ];then
  ./polling ${DB_HOST} ${DB_PORT} ${DB_USERNAME} ${DB_PASSWORD} ${DB_DATABASE} ${JG_MAX_RUNNING} ${JG_DATA_DIR} ${JG_NAME}
else
  ./polling ${DB_HOST} ${DB_PORT} ${DB_USERNAME} ${DB_PASSWORD} ${DB_DATABASE} ${JG_MAX_RUNNING} ${JG_DATA_DIR} ${JG_NAME} > /dev/null &
fi

sleep 1;
polling_name=`ps -e | grep polling | awk '{print $4}'`
if [ "${polling_name}" == "polling" ];then
  echo "[Judge is OK] Server has started to judge!"
else
  echo "[Judge starting Failed] Please check config in LDUOnlineJudge/.env"
fi
