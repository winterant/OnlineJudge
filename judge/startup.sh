#!/bin/bash

ps -e | grep polling | awk '{print "kill -9 " $1}' | sh

cd `dirname $0`
source ./judge.conf

if [ ! -d ./program ]; then
  mkdir ./program
fi

g++ -std=c++11 ./cpp/polling.cpp -o ./program/polling -lmysqlclient
g++ -std=c++11 ./cpp/judge.cpp  -o  ./program/judge   -lmysqlclient

cd ./program
if [ "$1" == "debug" ];then
  ./polling ${DB_HOST} ${DB_PORT} ${DB_USERNAME} ${DB_PASSWORD} ${DB_DATABASE} ${JG_max_running} ${JG_DATA_DIR}
else
  ./polling ${DB_HOST} ${DB_PORT} ${DB_USERNAME} ${DB_PASSWORD} ${DB_DATABASE} ${JG_max_running} ${JG_DATA_DIR} > /dev/null &
fi

sleep 1;
polling_name=`ps -e | grep polling | awk '{print $4}'`
if [ "${polling_name}" == "polling" ];then
  echo "[Judge is OK] Server has started to judge!"
else
  echo "[Judge Failed] Please check host or user for database in LDUOnlineJudge/judge/judge.conf"
fi
