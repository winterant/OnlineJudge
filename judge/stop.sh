#!/bin/bash

ps -e | grep judge | awk '{print "kill -9 " $1}' | sh
ps -e | grep polling | awk '{print "kill -9 " $1}' | sh
echo "polling: Stoped."
