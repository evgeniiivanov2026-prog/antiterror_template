export const queryKeys = {
  feed: (tab: "for-you" | "following") => ["feed", tab] as const,
  post: (id: string) => ["post", id] as const,
  replies: (id: string) => ["replies", id] as const,
  profile: (username: string) => ["profile", username] as const,
  userPosts: (username: string, filter: string) =>
    ["user-posts", username, filter] as const,
  search: (q: string) => ["search", q] as const,
  trending: ["trending"] as const,
  suggestions: ["suggestions"] as const,
  notifications: ["notifications"] as const,
  bookmarks: ["bookmarks"] as const,
};
