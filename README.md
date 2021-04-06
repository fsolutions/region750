#REGION750 CONTROL PANEL

### Сервис для клиентов и сотрудников компании Танаис

---

##### Установка проекта

1. Установка backend пакетов
```
> composer install
```  
2. Установка frontend пакетов
```
> npm i
```  
3. Генерация ключей laravel
```
> php artisan key:generate
> php artisan passport:install
```
4. Создаем пустую БД
5. Создаем .env файл, вписываем настройки
6. Накатываем миграции
```
> php artisan migrate
```
7. Направляем сервер на папку /public проекта
8. Запускаем webpack одной из команд package.json


##### Кэши Laravel

Чистка кэша конфига
```
> php artisan config:cache  
> php artisan config:clear  
```    

Чистка кэша маршрутизатора  
```
> php artisan route:cache  
> php artisan route:clear  
```    

Чистка общего кэша  
```
> php artisan cache:clear  
```    

##### Если пути до загруженных файлов отдают 404 ошибку

1. Удаляем папку app/public/storage
2. Далее 
```
> php artisan storage:link  
```    
3. Проверяем, заново запустив php artisan serve
```
> php artisan serve  
```

##### Права пользователей
1. Привязка роли к пользователю
```
$user->roles()->attach('role_name');
```
2. Проверка на существование роли у пользователя
```
$user->hasRole('role_name');
```
3. Использование в blade
```
@role('role_name')
 {{role_name}}
@endrole 
```
4. Использовать в middleware
```
Route::group(['middleware' => 'role:manager'], function() {
   Route::get('/dashboard', function() {
      return 'Добро пожаловать, Менеджер проекта';
   });
});
```

--------------------------------------------------------------------------------------------

### Elasticsearch

#### Инструкция по установке.
1.Скачать ES сервер:
````
https://www.elastic.co/downloads/past-releases/elasticsearch-7-10-2
````

2.Запустить ES. Инструкция по запуску:
````
https://www.elastic.co/downloads/elasticsearch
````

3.Выполнить команду, которая создаст индексы и импортирует данные. 
```
php artisan elastic:create 
```

####Комманды для работы с ES: 

Драйвер:
````
https://github.com/babenkoivan/scout-elasticsearch-driver
````

Создание конфига индекса:
````
php artisan elastic:custom-create  - создание конфига индексов для всех моделей
php artisan make:index-configurator App\Bundles\Elasticsearch\{название_индекса}IndexConfigurator - для индивидуальной
````

Создание индекса:
```
php artisan scout:custom-import  - создание всех индексов
php artisan elastic:create-index App\Bundles\Elasticsearch\{название_индекса}IndexConfigurator - индивидуально
```

Удаление индекса:
```
php artisan elastic:custom-drop - удаление всех индексов
php artisan elastic:drop-index App\CompanyIndexConfigurator - индивидуально
```

Полное обновление. Удаляет все индексы, создает их заново, импортирует данные из бд.
```
php artisan elastic:fresh 
```

```
http://127.0.0.1:9200/contract/_mapping
http://127.0.0.1:9200/contract/_settings
http://127.0.0.1:9200/contract/_search
http://127.0.0.1:9200/_cluster/health
```
