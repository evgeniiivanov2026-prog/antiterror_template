import type { AuthorView, FeedItem, NotificationView, PostView, UserCard } from "@/types/social";

export const authorSelect = {
  id: true,
  username: true,
  displayName: true,
  avatarUrl: true,
  bio: true,
} as const;

export function postInclude(userId: string) {
  return {
    author: { select: authorSelect },
    parent: {
      select: {
        id: true,
        author: { select: { username: true } },
      },
    },
    _count: {
      select: {
        likes: true,
        reposts: true,
        replies: true,
        bookmarks: true,
      },
    },
    likes: { where: { userId }, select: { userId: true }, take: 1 },
    reposts: { where: { userId }, select: { userId: true }, take: 1 },
    bookmarks: { where: { userId }, select: { userId: true }, take: 1 },
  };
}

export type PostRecord = {
  id: string;
  content: string;
  mediaUrls: string[];
  createdAt: Date;
  author: AuthorView;
  parent: { id: string; author: { username: string } } | null;
  _count: {
    likes: number;
    reposts: number;
    replies: number;
    bookmarks: number;
  };
  likes: { userId: string }[];
  reposts: { userId: string }[];
  bookmarks: { userId: string }[];
};

export function toAuthor(author: AuthorView): AuthorView {
  return {
    id: author.id,
    username: author.username,
    displayName: author.displayName,
    avatarUrl: author.avatarUrl,
    bio: author.bio,
  };
}

export function toPostView(post: PostRecord): PostView {
  return {
    id: post.id,
    content: post.content,
    mediaUrls: post.mediaUrls,
    createdAt: post.createdAt.toISOString(),
    author: toAuthor(post.author),
    parent: post.parent
      ? { id: post.parent.id, authorUsername: post.parent.author.username }
      : null,
    counts: {
      likes: post._count.likes,
      reposts: post._count.reposts,
      replies: post._count.replies,
      bookmarks: post._count.bookmarks,
    },
    viewer: {
      liked: post.likes.length > 0,
      reposted: post.reposts.length > 0,
      bookmarked: post.bookmarks.length > 0,
    },
  };
}

export function toFeedItem(
  row: { kind: string; sort_at: Date; sort_id: string; reposter_id: string | null },
  post: PostRecord,
  reposter: AuthorView | null,
): FeedItem {
  const kind = row.kind === "repost" ? "repost" : "post";
  return {
    id: `${kind}:${row.sort_id}`,
    sortAt: new Date(row.sort_at).toISOString(),
    sortId: row.sort_id,
    kind,
    repostedBy: kind === "repost" && reposter ? toAuthor(reposter) : null,
    post: toPostView(post),
  };
}

type UserWithFollowState = AuthorView & {
  _count: { followers: number };
  followers: { followerId: string }[];
};

export function toUserCard(user: UserWithFollowState, viewerId: string): UserCard {
  return {
    ...toAuthor(user),
    followers: user._count.followers,
    viewerFollows: user.followers.length > 0,
    isMe: user.id === viewerId,
  };
}

export function toNotification(row: {
  id: string;
  type: NotificationView["type"];
  read: boolean;
  createdAt: Date;
  issuer: AuthorView;
  post: { id: string; content: string; author: { username: string } } | null;
}): NotificationView {
  return {
    id: row.id,
    type: row.type,
    read: row.read,
    createdAt: row.createdAt.toISOString(),
    issuer: toAuthor(row.issuer),
    post: row.post
      ? {
          id: row.post.id,
          content: row.post.content,
          authorUsername: row.post.author.username,
        }
      : null,
  };
}
