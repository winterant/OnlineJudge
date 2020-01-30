#!/bin/bash

ps -e | grep polling | awk '{print "kill -9 " $1}' | sh
echo "polling: Stoped."
