#!/usr/bin/env bash

while [ true ]
do
  sudo -EH -u www-data bash -c "php bin/console schedule:run" &
  sleep 60
done
