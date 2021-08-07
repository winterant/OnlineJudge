#!/bin/bash

polling_name=$(ps -e | grep polling | awk '{print $4}')
if [[ "${polling_name}" != "" ]];then
    ps -e | grep polling | awk '{print "kill -9 " $1}' | sh
    echo -e "[Stopped judging processes]"
fi
