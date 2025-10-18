#!/bin/bash

./scripts/build.sh

cp -r -f ./client/StripeyHorse ~/Code/data-garden/app/Services/RateShopper
cp -r -f ./builds/stripey_horse_arm64 ~/Code/data-garden/resources/binaries/stripey_horse_arm64
cp -r -f ./builds/stripey_horse_amd64 ~/Code/data-garden/resources/binaries/stripey_horse_amd64

