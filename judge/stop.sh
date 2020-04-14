#!/bin/bash

polling_name=`ps -e | grep polling | awk '{print $4}'`
if [ "${polling_name}" == "polling" ];then
    ps -e | grep polling | awk '{print "kill -2 " $1}' | sh
    echo -e "Closing all judge processes, please wait a while!"
fi
