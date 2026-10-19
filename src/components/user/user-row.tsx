import Link from "next/link";
import { Avatar } from "@/components/ui/avatar";
import { FollowButton } from "@/components/user/follow-button";
import type { UserCard } from "@/types/social";

export function UserRow({ user }: { user: UserCard }) {
  return (
    <article className="flex items-start gap-3 px-4 py-3 hover:bg-white/[0.03]">
      <Link href={`/${user.username}`} aria-label={user.displayName}>
        <Avatar src={user.avatarUrl} name={user.displayName} size={40} />
      </Link>
      <div className="min-w-0 flex-1">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <Link href={`/${user.username}`} className="block truncate font-bold hover:underline">
              {user.displayName}
            </Link>
            <p className="truncate text-sm text-muted">@{user.username}</p>
          </div>
          {user.isMe ? (
            <span className="rounded-full border border-border px-3 py-1 text-sm text-muted">Вы</span>
          ) : (
            <FollowButton userId={user.id} initialFollowing={user.viewerFollows} />
          )}
        </div>
        {user.bio ? <p className="mt-1 line-clamp-2 text-[15px] leading-5">{user.bio}</p> : null}
      </div>
    </article>
  );
}
