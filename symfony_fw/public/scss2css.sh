#!/bin/bash

clear

echo Scss2css - Linux

source="$(dirname ${BASH_SOURCE[0]})"

sass --watch $source/scss:$source/css --style compressed