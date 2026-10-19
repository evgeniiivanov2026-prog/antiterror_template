# Pulse

Микроблог в духе X: три колонки, тёмная тема, лента «Для вас» / «Подписки», ответы, лайки, репосты, закладки, подписки и уведомления.

## Стек

- Next.js 15 (App Router, Server Actions, Server Components)
- TypeScript strict
- Tailwind CSS 4 и компоненты в стиле shadcn/ui (Radix, CVA)
- PostgreSQL и Prisma ORM 7
- TanStack Query: бесконечная лента и оптимистичные лайки, репосты и закладки
- Auth.js v5, JWT-сессия, вход по email или @username
- Zod на серверных экшенах и API

## Запуск

Нужны Node.js 22 и PostgreSQL 16.

```bash
docker compose up -d
cp .env.example .env
# впишите AUTH_SECRET: openssl rand -base64 32
npm install
npx prisma migrate dev
npm run db:seed
npm run dev
```

Приложение: http://localhost:3000

`npm run db:seed` очищает пользователей и заново наполняет демо-данными. В `NODE_ENV=production` сид не запустится без `SEED_CONFIRM=yes`.

## Демо-аккаунты

Пароль у всех: `password123`

| Email | Username |
| --- | --- |
| ada@pulse.dev | @ada |
| linus@pulse.dev | @linus |
| maya@pulse.dev | @maya |
| kenji@pulse.dev | @kenji |
| sofia@pulse.dev | @sofia |

У @ada уже есть подписки, закладки и непрочитанные уведомления.

## Как устроено

- Лента читается одним SQL с курсором `(createdAt, id)`, затем посты, счётчики и состояние текущего пользователя подтягиваются двумя запросами, без N+1.
- «Подписки» смешивают свои посты, посты людей, на которых вы подписаны, и их репосты. Курсор общий для обоих источников.
- Лайк, репост и закладка меняют кэш TanStack Query до ответа сервера и откатываются, если запрос не прошёл.
- Мутации проверяют сессию и проходят через Zod. Чужой пост удалить нельзя, на себя подписаться нельзя.
