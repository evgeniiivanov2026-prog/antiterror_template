export type PublicUser = {
  id: string;
  username: string;
  displayName: string;
  avatarUrl: string | null;
  bio?: string | null;
};

export type SessionUser = {
  id: string;
  username: string;
  displayName: string;
  avatarUrl: string | null;
  email?: string | null;
};

export type PostCounts = {
  likes: number;
  reposts: number;
  replies: number;
};

export type ViewerState = {
  liked: boolean;
  reposted: boolean;
  bookmarked: boolean;
};

export type SerializedPost = {
  id: string;
  content: string;
  mediaUrls: string[];
  createdAt: string;
  parentId: string | null;
  author: PublicUser;
  counts: PostCounts;
  viewer: ViewerState;
};

export type FeedPage = {
  items: SerializedPost[];
  nextCursor: string | null;
};

export type ProfileUser = PublicUser & {
  bio: string | null;
  coverUrl: string | null;
  createdAt: string;
  counts: {
    posts: number;
    followers: number;
    following: number;
  };
  viewer: {
    isSelf: boolean;
    isFollowing: boolean;
  };
};

export type NotificationItem = {
  id: string;
  type: "LIKE" | "REPOST" | "REPLY" | "FOLLOW";
  read: boolean;
  createdAt: string;
  issuer: PublicUser;
  post: { id: string; content: string } | null;
};

export type TrendingTopic = {
  name: string;
  posts: number;
};

export type SuggestionUser = PublicUser & {
  bio: string | null;
  isFollowing: boolean;
};
