FROM webdevops/php-nginx-dev:7.4 as development
ENV WEB_DOCUMENT_ROOT=/app/public
ENV COMPOSER_VERSION=2
ENV NODE_VERSION=18

RUN apt-get update && apt-get install -y bash curl && \
    echo "PS1='\[\e[94m\]workspace>\[\e[m\] '\n" > /home/application/.bashrc

RUN su - application -c "curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash"

#COPY ./nginxDev.conf /opt/docker/etc/nginx/vhost.common.conf

USER application
WORKDIR /app


FROM webdevops/php-nginx:7.4 as production
ENV WEB_DOCUMENT_ROOT=/app/public

USER application
WORKDIR /app

COPY --chown=application:application . /app
RUN composer install --no-progress

EXPOSE 80
