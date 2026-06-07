# --- Bước 1: Biên dịch Frontend Assets (Vite) ---
FROM node:20-alpine AS node-builder
WORKDIR /app

# Cài đặt các thư viện hệ thống cần thiết cho native modules (tailwindcss/oxide)
RUN apk add --no-cache python3 make g++ libc6-compat

COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Bước 2: Khởi chạy Laravel App với PHP và Nginx ---
FROM richarvey/nginx-php-fpm:3.1.6

# Sao chép mã nguồn của dự án vào thư mục chạy của container
COPY . /var/www/html

# Sao chép các tệp assets đã được biên dịch từ Bước 1
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Thiết lập các biến môi trường cho Richarvey Image
ENV AUDIT_LEVEL=none
ENV HIDE_NGINX_HEADERS=yes
ENV WEBROOT=/var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER=1

# Cài đặt các thư viện PHP cần thiết (không bao gồm dev dependencies)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Cấp quyền ghi cho các thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
