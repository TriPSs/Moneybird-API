#!/usr/bin/env node
'use strict';
var Path = require('path');

require('shelljs/global');
set('-e');

// Clear old docs
exec('npm run swagger bundle --        -o docs/swagger.json');
exec('npm run swagger bundle -- --yaml -o docs/swagger.yaml');

