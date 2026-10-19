export type FeedTab = "for-you" | "following";
export type ProfileTab = "posts" | "replies" | "media" | "likes";
export type ConnectionTab = "followers" | "following";

export type AuthorView = {
  id: string;
  username: string;
  displayName: string;
  avatarUrl: string | null;
  bio: string;
};

export type PostView = {
  id: string;
  content: string;
  mediaUrls: string[];
  createdAt: string;
  author: AuthorView;
  parent: { id: string; authorUsername: string } | null;
  counts: {
    likes: number;
    reposts: number;
    replies: number;
    bookmarks: number;
  };
  viewer: {
    liked: boolean;
    reposted: boolean;
    bookmarked: boolean;
  };
};

export type FeedItem = {
  id: string;
  sortAt: string;
  sortId: string;
  kind: "post" | "repost";
  repostedBy: AuthorView | null;
  post: PostView;
};

export type CursorPage<T> = {
  items: T[];
  nextCursor: string | null;
};

export type FeedPage = CursorPage<FeedItem>;

export type UserCard = AuthorView & {
  followers: number;
  viewerFollows: boolean;
  isMe: boolean;
};

export type Profile = AuthorView & {
  coverUrl: string | null;
  createdAt: string;
  counts: { posts: number; followers: number; following: number };
  viewerFollows: boolean;
  isMe: boolean;
};

export type NotificationView = {
  id: string;
  type: "LIKE" | "REPOST" | "REPLY" | "FOLLOW";
  read: boolean;
  createdAt: string;
  issuer: AuthorView;
  post: { id: string; content: string; authorUsername: string } | null;
};

export type Me = AuthorView & {
  createdAt: string;
  counts: { posts: number; followers: number; following: number };
};

export type Trend = {
  tag: string;
  count: number;
};

export type SearchResults = {
  users: UserCard[];
  posts: CursorPage<PostView>;
};

export type ActionResult<T> =
  | { ok: true; data: T }
  | { ok: false; error: string };
