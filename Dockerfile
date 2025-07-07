# Gunakan image PHP resmi dari Docker Hub
FROM php:8.1-cli

# Set folder kerja di dalam container
WORKDIR /app

# Salin semua file dari repo ke container
COPY . /app

# Install ekstensi cURL jika dibutuhkan (biasanya untuk API Mikrotik)
RUN docker-php-ext-install curl

# Buka port 10000 (wajib di Render)
EXPOSE 10000

# Jalankan built-in PHP server
CMD ["php", "-S", "0.0.0.0:10000"]
