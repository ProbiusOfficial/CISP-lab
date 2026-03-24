FROM php:7.3-fpm-alpine

LABEL lab="ping-rce-demo"

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories  &&\
    apk add --update --no-cache nginx bash iputils

COPY ./service/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

COPY ./config/nginx.conf /etc/nginx/nginx.conf

COPY src /var/www/html

RUN mkdir -p /var/www/html/hhh /var/www/html/writable \
    && chmod 777 /var/www/html/writable \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 80

VOLUME ["/var/log/nginx"]

ENTRYPOINT [ "/docker-entrypoint.sh" ]
