#!/bin/bash

#mysql config
db_host="192.168.31.16"
db_port=3306
db_user="testuser"
db_pass="123456"
db_name="woj"
max_running=5



ps -e | grep polling | awk '{print "kill -9 " $1}' | sh



if [ ! -d "./program" ]; then
  mkdir ./program
fi

g++ -std=c++11 ./cpp/polling.cpp -o ./program/polling -lmysqlclient
g++ -std=c++11 ./cpp/judge.cpp  -o  ./program/judge   -lmysqlclient

cd ./program
if [ "$1" == "log" ];then
  ./polling ${db_host} ${db_port} ${db_user} ${db_pass} ${db_name} ${max_running}
else
  ./polling ${db_host} ${db_port} ${db_user} ${db_pass} ${db_name} ${max_running} > /dev/null &
fi
