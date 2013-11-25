# Yii AmoCRM

**EAmoCRM** это расширение для **Yii PHP framework** которое выступает в качестве
простого прокси для обращения к API сайта [amoCRM](https://www.amocrm.ru/add-ons/api.php).
Структуры и данных для передачи нелогичны, за дополнительными разъяснениями
можно обратится к официальный документации [amoCRM](https://www.amocrm.ru/add-ons/api.php).

## Требования:

Yii Framework 1.1.0 или новее

## Установка:

- Скопировать папку `EAmoCRM` в `protected/extensions`
- Добавить в секцию `components` конфигурационного файла:

```php
<?php
    'amocrm' => array(
        'class' => 'application.extensions.EAmoCRM.EAmoCRM',
        'subdomain' => 'example', // Персональный поддомен на сайте amoCRM
        'login' => 'login@mail.com', // Логин на сайте amoCRM
        'password' => '123456', // Пароль на сайте amoCRM
        'hash' => '00000000000000000000000000000000', // Вместо пароля можно использовать API ключ
    ),
```

## Пример использования:

```php
<?php
    // Проверка авторизации на сайте amoCRM
    $result = Yii::app()->amocrm->ping();

    // Получение 1 страницы со списком контактов, >на странице 20 записей
    $result = Yii::app()->amocrm->listContacts(1, 20);
```

## Доступные методы

* Общее
    * `ping()` - Проверка авторизации
* Контакты
    * `listContacts($page, $onpage)` - Получение страницы со списком контактов
    * `searchContacts($keyword, $page, $onpage)` - Поиск контактов
    * `getContact($id)` - Получение детальной страницы контакта
    * `addContact($data)` - Добавление контакта
    * `editContact($id, $data)` - Редактирование контакта
    * `deleteContact($id)` - Удаление контакта
* Сделки
    * `listDeals($page, $onpage)` - Получение страницы со списком сделок
    * `searchDeals($keyword, $page, $onpage)`- Поиск сделок
    * `addDeal($data)` - Добавление сделки
    * `editDeal($id, $data)` - Редактирование сделки
    * `deleteDeal($id)` - Удаление сделки
* Примечания
    * `addContactNote($id, $message)` - Добавление примечания к контакту
    * `addDealNote($id, $message)` - Добавление примечания к сделке
    * `editNote($id, $message)` - Редактирование примечания
    * `deleteNote($id)` - Удаление примечания
* Задачи
    * `addTask($id, $message, $date, $type)` - Добавление простой задачи
    * `addContactTask($id, $contact, $message, $date, $type)` - Добавление задачи связанной с контактом
    * `addDealTask($id, $deal, $message, $date, $type)` - Добавление задачи связанной со сделкой
    * `editTask($task, $id, $message, $date, $type)` - Редактирование простой задачи
    * `editContactTask($task, $id, $contact, $message, $date, $type)` - Редактирование задачи связанной с контактом
    * `editDealTask($task, $id, $deal, $message, $date, $type)` - Редактирование задачи связанной со сделкой
    * `deleteTask($task)` - Удаление задачи
    * `completeTask($task)` - Выполнение задачи

---
Шлю особые лучики ненависти к компании QSOFT и их криворуким кодерам ^_^.
