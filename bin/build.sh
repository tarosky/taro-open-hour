#!/usr/bin/env bash

set -e

# Set variables.
PREFIX="refs/tags/"
VERSION=${1#"$PREFIX"}

# Build files
composer install --no-dev --prefer-dist
npm install
npm run package

# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php

# Convert readme version.
sed -i.bak "s/^Version: .*/Version: ${VERSION}/g" ./taro-open-hour.php
sed -i.bak "s/^Stable tag: .*/Stable tag: ${VERSION}/g" ./readme.txt
