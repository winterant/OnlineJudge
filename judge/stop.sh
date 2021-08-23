#!/bin/bash

polling_name=$(ps -e | grep polling | awk '{print $4}')
if [[ "${polling_name}" != "" ]];then
    ps -e | grep polling | awk '{print "kill -15 " $1}' | sh
    echo -e "[Stopped judgement processes]"
fi
