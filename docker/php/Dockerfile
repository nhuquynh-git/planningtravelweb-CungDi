
FROM php:8.4-apache

# Cài đặt thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install \
    intl \
    mbstring \
    mysqli \
    pdo_mysql \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Bật Apache modules
RUN a2enmod rewrite

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thư mục làm việc
WORKDIR /var/www/html

# Apache trỏ đến thư mục public của CodeIgniter
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Cập nhật DocumentRoot
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/000-default.conf

# Cho phép Apache đọc .htaccess trong public
RUN printf '\n<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' >> /etc/apache2/apache2.conf

# Tắt cảnh báo ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
