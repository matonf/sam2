#!/bin/bash

API="XXXXXXXXXXXXXXXXXX"
MSG="$1"
TITLE="$2"

curl -u $API: https://api.pushbullet.com/v2/pushes -d type=note -d title="$TITLE" -d body="$MSG"
