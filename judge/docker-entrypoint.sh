#!/bin/bash

set -ex

DB_HOST=${DB_HOST:-host.docker.internal}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-lduoj}
DB_USERNAME=${DB_USERNAME:-oj_user}
DB_PASSWORD=${DB_PASSWORD:-OurFutrue2045}
JG_DATA_DIR=${JG_DATA_DIR:-/testdata}
JG_NAME=${JG_NAME:-Default}
JG_MAX_RUNNING=${JG_MAX_RUNNING:-2}
echo -e "[Test data location] ${JG_DATA_DIR}"

# 编译判题端源码
g++ ./cpp/polling.cpp -o ./polling -std=c++11 -lmysqlclient 2>&1
g++ ./cpp/judge.cpp  -o  ./judge -std=c++11 -lmysqlclient 2>&1

sleep 10  # waiting for mysql to start.

./polling "${DB_HOST}" "${DB_PORT}" "${DB_USERNAME}" "${DB_PASSWORD}" "${DB_DATABASE}" "${JG_MAX_RUNNING}" "${JG_DATA_DIR}" "${JG_NAME}"
