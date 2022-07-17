#!/bin/sh
npm run build
php bin/console cache:clear && php bin/console cache:warmup 
rsync -av ./ u108623200@access913503391.webspace-data.io:~/sunuLegislatives2022 --include=public/build --exclude-from=.gitignore --exclude=".*"