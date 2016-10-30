#!/bin/bash
git pull origin master
php composer.phar install
(cd db; ../vendor/bin/propel config:convert) && \
(cd db; ../vendor/bin/propel migration:migrate) && \
(cd db; ../vendor/bin/propel migration:diff) && \
(cd db; ../vendor/bin/propel migration:migrate) && \
(cd db; ../vendor/bin/propel model:build)
php composer.phar dump-autoload -o
php tools/GenerateApiSchema.php
nginx -s reload
systemctl restart uthackers_app

sleep 5s

res=`curl -f http://localhost 2>&1`
res=`echo "$res" | grep 22`
if [ -z "$res" ]; then
    : curl -X POST --data-urlencode 'payload={"channel": "#test", "username": "deploy_bot", "text": "deploy success"}' https://hooks.slack.com/services/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
else
    : curl -X POST --data-urlencode 'payload={"channel": "#test", "username": "deploy_bot", "text": "deploy failed"}' https://hooks.slack.com/services/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
fi
