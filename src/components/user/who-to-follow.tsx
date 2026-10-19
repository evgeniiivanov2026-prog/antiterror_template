import Link from "next/link";
import { FollowButton } from "@/components/user/follow-button";
import { UserAvatar } from "@/components/user/user-avatar";
import type { SuggestionUser } from "@/types";

export function WhoToFollow({ users }: { users: SuggestionUser[] }) {
  return (
    <section className="overflow-hidden rounded-2xl bg-zinc-950">
      <h2 className="px-4 py-3 text-xl font-extrabold">Кого читать</h2>
      {users.length === 0 ? (
        <p className="px-4 pb-4 text-sm text-muted">Рекомендации появятся позже.</p>
      ) : (
        users.map((user) => (
          <div key={user.id} className="flex items-center gap-3 px-4 py-3">
            <Link href={`/${user.username}`}>
              <UserAvatar name={user.displayName} src={user.avatarUrl} />
            </Link>
            <div className="min-w-0 flex-1">
              <Link href={`/${user.username}`} className="block truncate font-bold hover:underline">
                {user.displayName}
              </Link>
              <p className="truncate text-sm text-muted">@{user.username}</p>
            </div>
            <FollowButton userId={user.id} initialFollowing={user.isFollowing} />
          </div>
        ))
      )}
    </section>
  );
}
