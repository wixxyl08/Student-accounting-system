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

### Требования

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- MySQL 8+ или MariaDB 10+
- Git

### Шаги

**1. Клонировать проект**

```bash
git clone https://github.com/wixxyl08/Student-accounting-system.git
cd Student-accounting-system
```

**2. Установить зависимости**

```bash
composer install
npm install
```

**3. Настроить .env**

```bash
cp .env.example .env
php artisan key:generate
```

Открой `.env` и настрой БД:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_system
DB_USERNAME=root
DB_PASSWORD=
```

**4. Создать БД**

```sql
CREATE DATABASE student_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**5. Запустить миграции и сидеры**

```bash
php artisan migrate --seed
```

**6. Создать симлинк для файлов**

```bash
php artisan storage:link
```

**7. Запустить**

```bash
# Терминал 1 — Laravel
php artisan serve

# Терминал 2 — Vite
npm run dev
```

**8. Открыть в браузере**

```
http://127.0.0.1:8000
```

## Тестовые учётные записи

| Логин | Пароль | Роль |
|---|---|---|
| `admin` | `admin123` | Администратор |
| `methodist` | `methodist123` | Методист |

## Структура проекта

```
app/
├── Exports/             — экспорты XLSX
├── Http/
│   ├── Controllers/Api/ — контроллеры
│   ├── Middleware/      — CheckRole
│   ├── Requests/        — валидация
│   └── Resources/       — API-ответы
├── Models/              — модели
└── Observers/           — observers (журнал)

database/
├── migrations/          — миграции
└── seeders/             — сидеры

resources/js/
├── api/                 — axios-запросы
├── components/          — компоненты
├── layouts/             — MainLayout
├── router/              — маршруты
├── stores/              — Pinia
└── views/               — страницы
```

## Основные API-эндпоинты

| Метод | URL | Описание |
|---|---|---|
| POST | `/api/login` | Вход |
| POST | `/api/logout` | Выход |
| GET | `/api/me` | Текущий пользователь |
| — | `/api/organizations` | CRUD организаций |
| — | `/api/employees` | CRUD сотрудников |
| — | `/api/educations` | CRUD образования |
| — | `/api/programs` | CRUD программ |
| — | `/api/groups` | CRUD групп |
| — | `/api/enrollments` | CRUD зачислений |
| GET | `/api/notifications` | Уведомления |
| GET | `/api/contracts` | Реестр договоров |
| GET | `/api/contracts/{id}/download-docx` | Скачать DOCX |
| GET | `/api/contracts/{id}/download-pdf` | Скачать PDF |
| GET | `/api/export/organizations` | Экспорт XLSX |
| — | `/api/admin/users` | Управление пользователями |
| GET | `/api/admin/activity-logs` | Журнал действий |
| GET | `/api/dashboard` | Статистика |