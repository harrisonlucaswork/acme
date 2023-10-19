FROM php:8.2-cli

RUN ln -s /usr/local/bin/php /usr/bin/php
RUN apt-get update && apt-get -y upgrade

# Install application required packages
RUN apt-get -y install unzip zip jq git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ARG BUILD_ENVIRONMENT
ENV BUILD_ENVIRONMENT=${BUILD_ENVIRONMENT:-development}
ARG DEVPKGS="less procps vim-nox iputils-ping net-tools"

RUN case "$BUILD_ENVIRONMENT" in \
      test|testing|dev|development) \
        pecl install xdebug-3.1.5 && docker-php-ext-enable xdebug; \
        apt-get update && apt-get -y install ${DEVPKGS}; \
        apt-get clean && rm -rf /var/lib/apt/lists/*; \
        ;; \
    esac

# Deploy php.ini
COPY ./configs/php-8.2-dev.ini /usr/local/etc/php/php.ini
COPY ./configs/php-xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Make required directory
RUN mkdir -p /acme

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Setup dev user
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID
RUN if [ "$BUILD_ENVIRONMENT" = "development" ]; then \
        groupadd --gid $USER_GID $USERNAME \
        && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME \
        && apt-get update \
        && apt-get install -y sudo \
        && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
        && chmod 0440 /etc/sudoers.d/$USERNAME \
    ; fi

USER $USERNAME

WORKDIR /acme

CMD ["tail", "-f", "/dev/null"]
