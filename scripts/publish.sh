#!/usr/bin/env bash

echo "Attempting to publish $1 of zpl-to-png" 
git tag $1 
git push origin $1
open https://packagist.org/packages/faerber/zpl-to-png

