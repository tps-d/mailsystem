### mailsystem

php >=7.3

- `composer install`
- `cp .env.example .env`
- 配置.env数据库
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan key:generate`


### crontab

`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`

### 常驻进程

`php artisan queue:work --queue=message-dispatch`
`php artisan queue:work --queue=webhook-process`


### 后台账户
默认用户 admin 密码123456
