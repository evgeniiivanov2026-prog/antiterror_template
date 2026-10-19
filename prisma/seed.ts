import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  await prisma.notification.deleteMany();
  await prisma.postHashtag.deleteMany();
  await prisma.hashtag.deleteMany();
  await prisma.bookmark.deleteMany();
  await prisma.like.deleteMany();
  await prisma.repost.deleteMany();
  await prisma.follow.deleteMany();
  await prisma.post.deleteMany();
  await prisma.user.deleteMany();

  const passwordHash = await bcrypt.hash("demo1234", 12);

  const [demo, nova, lex, mira, kai, iris] = await prisma.$transaction([
    prisma.user.create({
      data: {
        username: "demo",
        displayName: "Демо Пользователь",
        email: "demo@vox.app",
        passwordHash,
        bio: "Тестовый аккаунт Vox. Лайкайте, отвечайте, подписывайтесь.",
      },
    }),
    prisma.user.create({
      data: {
        username: "nova",
        displayName: "Нова Ким",
        email: "nova@vox.app",
        passwordHash,
        bio: "Продуктовый дизайнер. Тёмные интерфейсы и типографика.",
      },
    }),
    prisma.user.create({
      data: {
        username: "lex",
        displayName: "Лекс Орлов",
        email: "lex@vox.app",
        passwordHash,
        bio: "Инженер. Next.js, Postgres и бесконечные ленты.",
      },
    }),
    prisma.user.create({
      data: {
        username: "mira",
        displayName: "Мира Волкова",
        email: "mira@vox.app",
        passwordHash,
        bio: "Пишу про медиа и город. Хэштеги обязательны.",
      },
    }),
    prisma.user.create({
      data: {
        username: "kai",
        displayName: "Кай Степанов",
        email: "kai@vox.app",
        passwordHash,
        bio: "Основатель. Ищу людей, которые делают продукты быстрее.",
      },
    }),
    prisma.user.create({
      data: {
        username: "iris",
        displayName: "Ирис Белова",
        email: "iris@vox.app",
        passwordHash,
        bio: "Фотографирую ночные города и редкие интерфейсы.",
      },
    }),
  ]);

  const posts = await prisma.$transaction([
    prisma.post.create({
      data: {
        authorId: nova.id,
        content:
          "Тёмная тема — это не просто чёрный фон. Это ритм границ, воздуха и одного акцентного цвета. #дизайн #ui",
      },
    }),
    prisma.post.create({
      data: {
        authorId: lex.id,
        content:
          "Курсорная пагинация в Prisma спасает ленту лучше offset. Берёте id, skip: 1 — и никаких прыжков при новых постах. #nextjs #postgres",
      },
    }),
    prisma.post.create({
      data: {
        authorId: mira.id,
        content:
          "Короткий пост побеждает длинную статью, если в нём есть угол. Сегодня все говорят про #ai, но мало кто про читаемость.",
      },
    }),
    prisma.post.create({
      data: {
        authorId: kai.id,
        content:
          "Если продукт не открывается за секунду, пользователь уже ушёл. Скорость — это фича, не оптимизация потом. #стартапы",
      },
    }),
    prisma.post.create({
      data: {
        authorId: iris.id,
        content:
          "Ночной город любит высокий контраст. Так же, как хорошая лента: чёрный холст, белый текст, одна вспышка цвета. #фото",
      },
    }),
    prisma.post.create({
      data: {
        authorId: demo.id,
        content:
          "Первый пост в Vox. Если вы это видите — стек живой: Next.js, Prisma, Auth.js и оптимистичные лайки. #vox",
      },
    }),
    prisma.post.create({
      data: {
        authorId: lex.id,
        content:
          "Server Actions + Zod = меньше API-шума. Но бесконечный скролл всё равно удобнее держать в TanStack Query. #typescript",
      },
    }),
    prisma.post.create({
      data: {
        authorId: nova.id,
        content:
          "Кнопка «Опубликовать» должна быть самой заметной на экране. Если её ищут — навигация уже проиграла. #дизайн",
      },
    }),
    prisma.post.create({
      data: {
        authorId: mira.id,
        content:
          "Тренд — это не хэштег, который кричит громче всех. Это тема, к которой люди возвращаются три дня подряд. #медиа",
      },
    }),
    prisma.post.create({
      data: {
        authorId: kai.id,
        content:
          "Подписка важнее алгоритма: если человек сам выбрал автора, лента сразу становится личной. #продукт",
      },
    }),
  ]);

  const [novaPost, lexPost, miraPost] = posts;

  await prisma.post.create({
    data: {
      authorId: demo.id,
      parentId: lexPost.id,
      content: "Согласен. Offset ломается в ту же секунду, как кто-то публикует новый пост сверху.",
    },
  });

  await prisma.post.create({
    data: {
      authorId: nova.id,
      parentId: miraPost.id,
      content: "И ещё: один сильный кадр лучше десяти общих слов. #дизайн",
    },
  });

  const followPairs = [
    [demo.id, nova.id],
    [demo.id, lex.id],
    [demo.id, mira.id],
    [nova.id, lex.id],
    [lex.id, nova.id],
    [mira.id, demo.id],
    [kai.id, demo.id],
    [iris.id, nova.id],
    [lex.id, kai.id],
  ] as const;

  await prisma.follow.createMany({
    data: followPairs.map(([followerId, followingId]) => ({
      followerId,
      followingId,
    })),
  });

  await prisma.like.createMany({
    data: [
      { userId: demo.id, postId: novaPost.id },
      { userId: demo.id, postId: lexPost.id },
      { userId: lex.id, postId: novaPost.id },
      { userId: mira.id, postId: lexPost.id },
      { userId: kai.id, postId: posts[5].id },
      { userId: iris.id, postId: miraPost.id },
    ],
  });

  await prisma.repost.createMany({
    data: [
      { userId: lex.id, postId: novaPost.id },
      { userId: demo.id, postId: kai.id === posts[3].authorId ? posts[3].id : posts[3].id },
    ],
  });

  await prisma.bookmark.createMany({
    data: [
      { userId: demo.id, postId: lexPost.id },
      { userId: demo.id, postId: posts[3].id },
    ],
  });

  const allPosts = await prisma.post.findMany({ select: { id: true, content: true } });
  for (const post of allPosts) {
    const tags = [...new Set((post.content.match(/#[\p{L}\p{N}_]+/gu) ?? []).map((tag) => tag.slice(1).toLowerCase()))];
    for (const name of tags) {
      const hashtag = await prisma.hashtag.upsert({
        where: { name },
        update: {},
        create: { name },
      });
      await prisma.postHashtag.create({
        data: { postId: post.id, hashtagId: hashtag.id },
      });
    }
  }

  await prisma.notification.createMany({
    data: [
      {
        recipientId: demo.id,
        issuerId: mira.id,
        type: "FOLLOW",
      },
      {
        recipientId: demo.id,
        issuerId: kai.id,
        type: "FOLLOW",
      },
      {
        recipientId: demo.id,
        issuerId: kai.id,
        type: "LIKE",
        postId: posts[5].id,
      },
    ],
  });
}

main()
  .then(async () => {
    await prisma.$disconnect();
  })
  .catch(async (error) => {
    console.error(error);
    await prisma.$disconnect();
    process.exit(1);
  });
