#!/bin/bash

polling_name=`ps -e | grep polling | awk '{print $4}'`
if [ "${polling_name}" == "polling" ];then
    ps -e | grep judge | awk '{print "kill -9 " $1}' | sh
    ps -e | grep polling | awk '{print "kill -9 " $1}' | sh
    echo -e "polling: Stoped."
fi
