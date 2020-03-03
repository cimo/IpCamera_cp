#!/bin/bash
sudo -u $1 xvfb-run --server-args="-screen 0 $2x24" java -jar $3/selenium_server.jar -host $4 -port $5