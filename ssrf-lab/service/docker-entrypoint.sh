#!/bin/sh

rm -f /docker-entrypoint.sh

if [ "$DASFLAG" ]; then
    INSERT_FLAG="$DASFLAG"
    export DASFLAG=no_FLAG
elif [ "$FLAG" ]; then
    INSERT_FLAG="$FLAG"
    export FLAG=no_FLAG
elif [ "$GZCTF_FLAG" ]; then
    INSERT_FLAG="$GZCTF_FLAG"
    export GZCTF_FLAG=no_FLAG
else
    INSERT_FLAG="flag{SSRF_LAB_DEMO}"
fi

echo "$INSERT_FLAG" | tee /flag
chmod 744 /flag

# 本机 Redis，供 dict:// / gopher:// 演示（仅监听 127.0.0.1）
redis-server --daemonize yes --bind 127.0.0.1 --port 6379

php-fpm & nginx &

echo "Running..."

tail -F /var/log/nginx/access.log /var/log/nginx/error.log
