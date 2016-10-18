git pull origin master
nginx -s reload
systemctl restart uthackers_app

sleep 5s

res = `curl -f http://localhost`
if [ $res == 0] ; then
    curl -X POST --data-urlencode 'payload={"channel": "#test", "username": "deploy_bot", "text": "deploy success"}' https://hooks.slack.com/services/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
else
    curl -X POST --data-urlencode 'payload={"channel": "#test", "username": "deploy_bot", "text": "deploy failed"}' https://hooks.slack.com/services/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
fi
