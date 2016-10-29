# Fresh Fridge サーバー側

## 環境
### Required
* \>= PHP7.0
* HTTPSサーバー (deploy.shは nginx + php-fpm 前提)
* pecl YAML
* mysqld

### 準備
```sh
./deploy.sh
cd db
../vendor/bin/propel sql:build
../vendor/bin/propel sql:insert
cd ..
./deploy.sh
php db/import_items.php \<db/import_items.csv
