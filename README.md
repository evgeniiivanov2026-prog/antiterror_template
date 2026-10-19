# Vox

Микроблоггинг-платформа в духе X.com: трёхколоночный тёмный интерфейс, бесконечная лента, оптимистичные лайки/репосты/закладки, подписки и уведомления.

## Стек

- Next.js 16 (App Router, Server Actions, Server Components)
- TypeScript (strict)
- Tailwind CSS + Radix UI
- PostgreSQL + Prisma
- Auth.js v5 (JWT, credentials)
- TanStack Query
- Zod

## Быстрый старт

1. Поднимите PostgreSQL (`docker compose up -d` или локальный инстанс).
2. Скопируйте `.env.example` в `.env` и укажите `DATABASE_URL` и `AUTH_SECRET`.
3. Установите зависимости и примените схему:

```bash
npm install
npx prisma db push
npm run db:seed
npm run dev
```

Откройте [http://localhost:3000](http://localhost:3000).

## Демо-аккаунт

- Логин: `demo` или `demo@vox.app`
- Пароль: `demo1234`

Остальные сиды (`nova`, `lex`, `mira`, `kai`, `iris`) используют тот же пароль.

## Возможности

- Регистрация и вход по email/нику
- Лента «Для вас» / «Подписки» с курсорной пагинацией
- Посты до 280 символов, ответы, медиа
- Лайки, репосты и закладки с оптимистичным UI
- Профили, подписки, поиск, тренды и уведомления
