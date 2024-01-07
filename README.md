

## Set Up
Pastikan versi PHP yang digunakan versi 8+.

1. Composer
Instal composer melalui link berikut [Download Composer](https://getcomposer.org/download/)
2. Database
Buat database dengan nama "sistem_pemesanan"
3. Clone Project
Buka terminal dan jalankan script berikut
```sh
https://github.com/Flemel1/sistem-pemesanan-barang.git
```
4. Setup environment
Buka project yang telah diclone kemudian buat file .env dan tambahkan kode berikut.
```sh
APP_NAME=GoTo
APP_ENV=local
APP_KEY=base64:gPzLv8kI4kOnBdbyEH9ZwYVOn2JWiGSLTit/w3FMu4E=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_pemesanan
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

CHATIFY_NAME=Chat
CHATIFY_ROUTES_PREFIX=chat

PUSHER_APP_ID=728607
PUSHER_APP_KEY=da6518e38a8e53e2be0e
PUSHER_APP_SECRET=cd543fedc585609d823f
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=ap1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

GOOGLE_MAPS_API_KEY="AIzaSyD5ToDx89t74vmn-jio8GD2XI003RkVK04"
```
5. Inisial Databse
Jalankan script berikut pada terminal.
```sh
php artisan key:generate
```
```sh
php artisan migrate
```
6. Jalankan project
Jalankan script berikut pada terminal.
```sh
php artisan serve
```
7. Daftar akun
Buka browser dan masukkan alamat http://127.0.0.1:8000
Halaman admin [Halaman Admin](http://127.0.0.1:8000/admin)
Halaman konsumen [Halaman Konsumen](http://127.0.0.1:8000)

