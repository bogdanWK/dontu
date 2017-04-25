#! /bin/bash
NAME=$(hostname)
MEMORY=$(free -m | awk 'NR==2{printf "%.2f%%", $3*100/$2 }')
DISK=$(df -h | awk '$NF=="/"{printf "%s", $5}')
CPU=$(top -bn1 | grep load | awk '{printf "%.2f%%", $(NF-2)}')

echo "{ \"ubt64\": { \"name\": \"$NAME\", \"cpu\": \"$CPU\", \"mem\": \"$MEMORY\", \"disk\": \"$DISK\" } }"
