#!/bin/bash

# 安装判题端所必需的环境

apt update && apt upgrade
apt -y install libmysqlclient-dev g++
apt -y install openjdk-8-jdk
