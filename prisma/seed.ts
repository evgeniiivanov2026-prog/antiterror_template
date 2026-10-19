import "dotenv/config";
import { hash } from "bcryptjs";
import { PrismaPg } from "@prisma/adapter-pg";
import { NotificationType, PrismaClient } from "../src/generated/prisma/client";

const connectionString = process.env.DATABASE_URL;
if (!connectionString) {
  throw new Error("DATABASE_URL is not set");
}

const prisma = new PrismaClient({
  adapter: new PrismaPg({ connectionString }),
});

const hoursAgo = (hours: number) => new Date(Date.now() - hours * 60 * 60 * 1000);
const avatar = (seed: string) => `https://api.dicebear.com/9.x/notionists/svg?seed=${seed}`;

async function main() {
  if (process.env.NODE_ENV === "production" && process.env.SEED_CONFIRM !== "yes") {
    throw new Error("Refusing to wipe a production database. Set SEED_CONFIRM=yes to continue.");
  }

  await prisma.user.deleteMany();
  const passwordHash = await hash("password123", 10);

  const [ada, linus, maya, kenji, sofia] = await Promise.all([
    prisma.user.create({
      data: {
        username: "ada",
        displayName: "Ада Ловелейс",
        email: "ada@pulse.dev",
        passwordHash,
        bio: "Пишу о языках, компиляторах и аккуратном коде.",
        avatarUrl: avatar("ada"),
        coverUrl: "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1600&q=80",
        createdAt: hoursAgo(900),
      },
    }),
    prisma.user.create({
      data: {
        username: "linus",
        displayName: "Линус Керн",
        email: "linus@pulse.dev",
        passwordHash,
        bio: "Ядро, инструменты и длинные треды.",
        avatarUrl: avatar("linus"),
        createdAt: hoursAgo(800),
      },
    }),
    prisma.user.create({
      data: {
        username: "maya",
        displayName: "Майя Чен",
        email: "maya@pulse.dev",
        passwordHash,
        bio: "Интерфейсы, которые не кричат.",
        avatarUrl: avatar("maya"),
        coverUrl: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80",
        createdAt: hoursAgo(700),
      },
    }),
    prisma.user.create({
      data: {
        username: "kenji",
        displayName: "Кендзи Мори",
        email: "kenji@pulse.dev",
        passwordHash,
        bio: "Музыка, города и ночные сборки.",
        avatarUrl: avatar("kenji"),
        createdAt: hoursAgo(600),
      },
    }),
    prisma.user.create({
      data: {
        username: "sofia",
        displayName: "София Рейес",
        email: "sofia@pulse.dev",
        passwordHash,
        bio: "Продукт, рост и живые сообщества.",
        avatarUrl: avatar("sofia"),
        createdAt: hoursAgo(500),
      },
    }),
  ]);

  const cursorPost = await prisma.post.create({
    data: {
      authorId: ada.id,
      createdAt: hoursAgo(48),
      content:
        "Собрала ленту на курсорной пагинации. Offset на больших таблицах читает лишние строки, курсор — нет. #typescript #postgres",
    },
  });
  const designPost = await prisma.post.create({
    data: {
      authorId: ada.id,
      createdAt: hoursAgo(30),
      content:
        "Тёмная тема по умолчанию — режим, в котором интерфейс меньше шумит. Один акцент, тонкие границы, много воздуха. #дизайн",
    },
  });
  const nightPost = await prisma.post.create({
    data: {
      authorId: ada.id,
      createdAt: hoursAgo(12),
      content: "Ночной стенд: плата, кофе и один зелёный тест. Иногда этого достаточно. #web",
      mediaUrls: [
        "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80",
      ],
    },
  });
  const optimisticPost = await prisma.post.create({
    data: {
      authorId: linus.id,
      createdAt: hoursAgo(40),
      content:
        "Если лайк не оптимистичный, человек нажимает второй раз. UI должен отвечать сразу, сервер — подтверждать. #web #typescript",
    },
  });
  await prisma.post.create({
    data: {
      authorId: linus.id,
      createdAt: hoursAgo(20),
      content:
        "Индекс по (createdAt, id) делает следующую страницу предсказуемой. Красивый ORDER BY без сюрпризов. #postgres",
    },
  });
  const columnsPost = await prisma.post.create({
    data: {
      authorId: maya.id,
      createdAt: hoursAgo(36),
      content:
        "Три колонки. Слева навигация, в центре разговор, справа контекст. Всё остальное можно убрать. #дизайн #nextjs",
    },
  });
  const designReply = await prisma.post.create({
    data: {
      authorId: maya.id,
      parentId: designPost.id,
      createdAt: hoursAgo(8),
      content: "И ещё плотность. Если карточка кричит рамкой, лента устаёт быстрее, чем читатель. @ada",
    },
  });
  const musicPost = await prisma.post.create({
    data: {
      authorId: kenji.id,
      createdAt: hoursAgo(28),
      content: "Ночная сборка и плейлист без слов. Город уже спит, а типы ещё нет. #музыка #typescript",
    },
  });
  await prisma.post.create({
    data: {
      authorId: kenji.id,
      createdAt: hoursAgo(6),
      content: "Хэштег — это дверь, не декорация. #музыка #дизайн",
    },
  });
  const productPost = await prisma.post.create({
    data: {
      authorId: sofia.id,
      createdAt: hoursAgo(24),
      content:
        "«Для вас» показывает мир. «Подписки» — людей, которых вы сами выбрали. Это разная редактура. #продукт",
    },
  });
  const optimisticReply = await prisma.post.create({
    data: {
      authorId: sofia.id,
      parentId: optimisticPost.id,
      createdAt: hoursAgo(10),
      content: "И кнопка должна помнить состояние, пока запрос в пути. Иначе оптимизм превращается в дёрганье.",
    },
  });
  const cursorReply = await prisma.post.create({
    data: {
      authorId: linus.id,
      parentId: cursorPost.id,
      createdAt: hoursAgo(18),
      content:
        "И не забыть составной курсор: время плюс id, иначе две записи в одну миллисекунду перепутают страницы. #postgres",
    },
  });

  await prisma.follow.createMany({
    data: [
      { followerId: ada.id, followingId: linus.id, createdAt: hoursAgo(70) },
      { followerId: ada.id, followingId: maya.id, createdAt: hoursAgo(69) },
      { followerId: ada.id, followingId: kenji.id, createdAt: hoursAgo(68) },
      { followerId: linus.id, followingId: ada.id, createdAt: hoursAgo(60) },
      { followerId: linus.id, followingId: sofia.id, createdAt: hoursAgo(59) },
      { followerId: maya.id, followingId: ada.id, createdAt: hoursAgo(55) },
      { followerId: maya.id, followingId: kenji.id, createdAt: hoursAgo(54) },
      { followerId: maya.id, followingId: sofia.id, createdAt: hoursAgo(53) },
      { followerId: kenji.id, followingId: maya.id, createdAt: hoursAgo(50) },
      { followerId: kenji.id, followingId: ada.id, createdAt: hoursAgo(49) },
      { followerId: sofia.id, followingId: ada.id, createdAt: hoursAgo(45) },
      { followerId: sofia.id, followingId: linus.id, createdAt: hoursAgo(44) },
      { followerId: sofia.id, followingId: maya.id, createdAt: hoursAgo(43) },
      { followerId: sofia.id, followingId: kenji.id, createdAt: hoursAgo(42) },
    ],
  });

  await prisma.like.createMany({
    data: [
      { userId: linus.id, postId: cursorPost.id, createdAt: hoursAgo(47) },
      { userId: maya.id, postId: designPost.id, createdAt: hoursAgo(29) },
      { userId: sofia.id, postId: columnsPost.id, createdAt: hoursAgo(30) },
      { userId: ada.id, postId: columnsPost.id, createdAt: hoursAgo(25) },
      { userId: ada.id, postId: musicPost.id, createdAt: hoursAgo(20) },
      { userId: kenji.id, postId: nightPost.id, createdAt: hoursAgo(11) },
      { userId: maya.id, postId: productPost.id, createdAt: hoursAgo(16) },
      { userId: linus.id, postId: optimisticReply.id, createdAt: hoursAgo(9) },
    ],
  });

  await prisma.repost.createMany({
    data: [
      { userId: kenji.id, postId: columnsPost.id, createdAt: hoursAgo(14) },
      { userId: sofia.id, postId: cursorPost.id, createdAt: hoursAgo(15) },
      { userId: ada.id, postId: musicPost.id, createdAt: hoursAgo(7) },
    ],
  });

  await prisma.bookmark.createMany({
    data: [
      { userId: ada.id, postId: columnsPost.id, createdAt: hoursAgo(22) },
      { userId: ada.id, postId: optimisticPost.id, createdAt: hoursAgo(19) },
      { userId: maya.id, postId: designPost.id, createdAt: hoursAgo(13) },
    ],
  });

  await prisma.notification.createMany({
    data: [
      {
        recipientId: ada.id,
        issuerId: linus.id,
        type: NotificationType.LIKE,
        postId: cursorPost.id,
        read: false,
        createdAt: hoursAgo(47),
      },
      {
        recipientId: ada.id,
        issuerId: maya.id,
        type: NotificationType.REPLY,
        postId: designReply.id,
        read: false,
        createdAt: hoursAgo(8),
      },
      {
        recipientId: ada.id,
        issuerId: sofia.id,
        type: NotificationType.FOLLOW,
        read: false,
        createdAt: hoursAgo(45),
      },
      {
        recipientId: ada.id,
        issuerId: sofia.id,
        type: NotificationType.REPOST,
        postId: cursorPost.id,
        read: true,
        createdAt: hoursAgo(15),
      },
      {
        recipientId: ada.id,
        issuerId: kenji.id,
        type: NotificationType.LIKE,
        postId: nightPost.id,
        read: false,
        createdAt: hoursAgo(11),
      },
      {
        recipientId: linus.id,
        issuerId: sofia.id,
        type: NotificationType.REPLY,
        postId: optimisticReply.id,
        read: false,
        createdAt: hoursAgo(10),
      },
      {
        recipientId: ada.id,
        issuerId: linus.id,
        type: NotificationType.REPLY,
        postId: cursorReply.id,
        read: true,
        createdAt: hoursAgo(18),
      },
      {
        recipientId: maya.id,
        issuerId: kenji.id,
        type: NotificationType.REPOST,
        postId: columnsPost.id,
        read: false,
        createdAt: hoursAgo(14),
      },
    ],
  });
}

main()
  .then(async () => {
    await prisma.$disconnect();
  })
  .catch(async (error: unknown) => {
    console.error(error);
    await prisma.$disconnect();
    process.exit(1);
  });
