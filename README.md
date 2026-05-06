# Лабораторная работа №8  
## Работа с Docker и настройка CI (GitHub Actions)

---

## 📌 Название лабораторной работы
**Настройка непрерывной интеграции (CI) с использованием Docker и GitHub Actions**

---

## 🎯 Цель работы
Изучить принципы непрерывной интеграции (CI), научиться создавать контейнеризированное PHP-приложение с использованием Docker и автоматизировать запуск тестов через GitHub Actions.

---

## 🧾 Задание
1. Создать Web-приложение на PHP.
2. Использовать SQLite в качестве базы данных.
3. Реализовать CRUD-операции через класс Database.
4. Создать систему шаблонов через класс Page.
5. Написать тесты для проверки функциональности.
6. Настроить Docker-контейнер для запуска приложения.
7. Настроить CI через GitHub Actions для автоматического запуска тестов.

---

## 📁 Структура проекта

```
containers08
├── site/
│   ├── modules/
│   │   ├── database.php
│   │   └── page.php
│   ├── templates/
│   │   └── index.tpl
│   ├── styles/
│   │   └── style.css
│   ├── config.php
│   └── index.php
├── sql/
│   └── schema.sql
├── tests/
│   ├── testframework.php
│   └── tests.php
├── Dockerfile
└── .github/workflows/main.yml
````
<img width="312" height="542" alt="image" src="https://github.com/user-attachments/assets/aa642098-f7dc-4735-971c-8b01a72f026e" />
---
## ⚙️ Реализация Web-приложения
### 📌 База данных (SQLite)
Создана таблица `page`:
```sql
CREATE TABLE page (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    content TEXT
);
````
Добавлены тестовые данные:
```sql
INSERT INTO page (title, content) VALUES ('Page 1', 'Content 1');
INSERT INTO page (title, content) VALUES ('Page 2', 'Content 2');
INSERT INTO page (title, content) VALUES ('Page 3', 'Content 3');
```

<img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/da59511d-50d8-41b1-9067-fc1a13a5847a" />

---

### 📌 Класс Database

Реализует работу с базой данных и содержит методы:

* `Execute($sql)` — выполнение SQL-запросов
* `Fetch($sql)` — получение данных
* `Create($table, $data)` — создание записи
* `Read($table, $id)` — чтение записи
* `Update($table, $id, $data)` — обновление записи
* `Delete($table, $id)` — удаление записи
* `Count($table)` — количество записей

---

### 📌 Класс Page

Отвечает за шаблонизацию HTML:

* `__construct($template)` — загрузка шаблона
* `Render($data)` — подстановка данных в шаблон

---

### 📌 Шаблон страницы

Используется файл `index.tpl`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>{{title}}</title>
    <link rel="stylesheet" href="/styles/style.css">
</head>
<body>

<h1>{{title}}</h1>
<p>{{content}}</p>

</body>
</html>
```

---

## 🧪 Тестирование

Создан собственный тестовый фреймворк, который позволяет:

* запускать тесты
* логировать результат
* подсчитывать успешные проверки

```php
<?php

function message($type, $message) {
    $time = date('Y-m-d H:i:s');
    echo "{$time} [{$type}] {$message}" . PHP_EOL;
}

function info($message) {
    message('INFO', $message);
}

function error($message) {
    message('ERROR', $message);
}

function assertExpression($expression, $pass = 'Pass', $fail = 'Fail'): bool {
    if ($expression) {
        info($pass);
        return true;
    }
    error($fail);
    return false;
}

class TestFramework {
    private $tests = [];
    private $success = 0;

    public function add($name, $test) {
        $this->tests[$name] = $test;
    }

    public function run() {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            if ($test()) {
                $this->success++;
            }
            info("End test {$name}");
        }
    }

    public function getResult() {
        return "{$this->success} / " . count($this->tests);
    }
}
```

### Проверяемые функции:

* подключение к базе данных
* количество записей
* создание записи
* чтение записи
* обновление записи
* удаление записи
* рендеринг страницы

---

## 🐳 Docker

Создан Dockerfile:

* PHP 7.4 FPM
* установка SQLite
* создание базы данных внутри контейнера
* копирование приложения

<img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/34b50fe0-2ab0-48c4-9749-cbc41e4a05a4" />

### 📌 Сборка образа:

```bash
docker build -t containers08 .
```

### 📌 Запуск контейнера:

```bash
docker create --name container containers08
docker start container
```

### 📌 Запуск тестов:

```bash
docker exec container php /var/www/html/tests/tests.php
```

---

## ⚙️ GitHub Actions (CI)

Настроен автоматический pipeline:

```yaml
name: CI

on:
  push:
    branches: [ main ]
  pull_request:

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Build Docker image
        run: docker build -t containers08 .

      - name: Create container
        run: docker create --name container containers08

      - name: Copy tests
        run: docker cp ./tests container:/var/www/html

      - name: Start container
        run: docker start container

      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php

      - name: Stop container
        run: docker stop container

      - name: Remove container
        run: docker rm container
```
<img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/17b05ab8-fa2f-4df9-9c7b-4adf07c2f221" />
---
## 🧪 Результат выполнения CI
После отправки изменений в репозиторий:
* GitHub автоматически собирает Docker образ
* запускает контейнер
* выполняет тесты
* выводит результат выполнения
Пример результата:
<img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/15f72688-a20e-410e-a63a-241f89a91536" />
---
## ❓ Ответы на контрольные вопросы
### 1. Что такое непрерывная интеграция?
Непрерывная интеграция (CI) — это практика автоматической сборки и тестирования проекта при каждом изменении кода в репозитории.
---
### 2. Для чего нужны юнит-тесты?
Юнит-тесты используются для проверки отдельных частей программы.
Они позволяют быстро обнаружить ошибки и должны запускаться при каждом изменении кода.
---
### 3. Как запускать тесты при Pull Request?
Необходимо добавить:
```yaml
on:
  push:
  pull_request:
```
---
### 4. Как удалять образы после выполнения тестов?
Добавить шаг:
```yaml
- name: Remove image
  run: docker rmi containers08
```
---
## 📌 Вывод
В ходе лабораторной работы было разработано PHP Web-приложение с использованием SQLite, реализованы тесты и настроена автоматическая проверка кода через GitHub Actions.
Также были изучены основы Docker и непрерывной интеграции (CI), что позволяет автоматизировать процесс тестирования и повысить надежность разработки.
```
