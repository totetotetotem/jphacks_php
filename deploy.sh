git pull origin master
systemctl restart uthackers_app
nginx -s reload

res = `curl -f http://localhost`
if [ $res == 0] ; then
fi
