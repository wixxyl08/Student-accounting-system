# Система учёта и регистрации обучающихся

Веб-приложение для автоматизации учёта обучающихся, организаций, программ обучения и формирования договоров.

## Стек технологий

- **Backend:** PHP 8.3 + Laravel 12
- **Frontend:** Vue 3 + Vite
- **База данных:** MySQL 26.7
- **Аутентификация:** Laravel Sanctum (токены)
- **API:** REST

## Возможности

- **Организации** — CRUD, реквизиты, контакты, привязка сотрудников
- **Сотрудники** — CRUD, привязка к организации или физлицо, статус «Активен/Уволен»
- **Образование** — записи с уровнем, заведением, годом, прикрепление файлов дипломов
- **Программы обучения** — CRUD, цена, периодичность повторного обучения, архив
- **Группы обучения** — CRUD, автогенерация названий, статусы
- **Зачисления** — связка сотрудника и группы, проверки, авто-расчёт даты повторного обучения
- **Уведомления** — список тех, кому пора проходить повторное обучение
- **Договоры** — генерация DOCX (PHPWord) + PDF (DomPDF), реестр, скачивание
- **Экспорт XLSX** — организации, сотрудники, группы, уведомления
- **Админка** — управление пользователями, журнал действий (Observer)
- **Роли** — администратор (полный доступ), методист (ограниченный)

## Установка

### Требования

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- MySQL 8+ или MariaDB 10+
- Git

### Шаги

1. **Клонировать проект:**
   ```bash
   git clone https://github.com/wixxyl08/Student-accounting-system.git
   cd Student-accounting-system

2. **Установить зависимости:**

   composer install
   npm install

3. **Настроить .env:**

   cp .env.example .env
   php artisan key:generate

   Открой .env и настрой БД:

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=student_system
   DB_USERNAME=root
   DB_PASSWORD=

4. **Создать БД:**

   CREATE DATABASE student_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

5. **Запустить миграции и сидеры:**

   php artisan migrate --seed

6. **Создать симлинк для файлов:**

   php artisan storage:link

7. **Запустить:**

   php artisan serve
   npm run dev

8. **Открыть в браузере:**

   http://127.0.0.1:8000

## Тестовые учётные записи

- admin / admin123 — Администратор
- methodist / methodist123 — Методист

## Структура проекта

app/Exports/ — экспорты XLSX
app/Http/Controllers/Api/ — контроллеры
app/Http/Middleware/ — CheckRole
app/Http/Requests/ — валидация
app/Http/Resources/ — API-ответы
app/Models/ — модели
app/Observers/ — observers (журнал)
database/migrations/ — миграции
database/seeders/ — сидеры
resources/js/api/ — axios-запросы
resources/js/components/ — компоненты
resources/js/layouts/ — MainLayout
resources/js/router/ — маршруты
resources/js/stores/ — Pinia
resources/js/views/ — страницы

## Основные API-эндпоинты

- POST /api/login — Вход
- POST /api/logout — Выход
- GET /api/me — Текущий пользователь
- /api/organizations — CRUD организаций
- /api/employees — CRUD сотрудников
- /api/educations — CRUD образования
- /api/programs — CRUD программ
- /api/groups — CRUD групп
- /api/enrollments — CRUD зачислений
- GET /api/notifications — Уведомления
- GET /api/contracts — Реестр договоров
- GET /api/contracts/{id}/download-docx — Скачать DOCX
- GET /api/contracts/{id}/download-pdf — Скачать PDF
- GET /api/export/organizations — Экспорт XLSX
- /api/admin/users — Управление пользователями
- GET /api/admin/activity-logs — Журнал действий
- GET /api/dashboard — Статистика

## Лицензия

Учебный проект.