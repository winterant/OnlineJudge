#!/bin/bash

polling_name=$(ps -e | grep polling | awk '{print $4}')
if [[ "${polling_name}" != "" ]];then
    echo -e "[Kill polling process]"
    echo -e "$(ps -e | grep polling)"
    ps -e | grep polling | awk '{print "kill -15 " $1}' | sh
    ps -e | grep polling | awk '{print "kill -9 " $1}' | sh
fi
