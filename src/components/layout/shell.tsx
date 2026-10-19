import { MobileCompose } from "@/components/post/compose-dialog";
import { MobileNav } from "@/components/layout/mobile-nav";
import { RightPanel } from "@/components/layout/right-panel";
import { Sidebar } from "@/components/layout/sidebar";
import type { Me, Trend, UserCard } from "@/types/social";

export function Shell({
  me,
  trends,
  suggestions,
  unread,
  children,
}: {
  me: Me;
  trends: Trend[];
  suggestions: UserCard[];
  unread: number;
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-black text-foreground">
      <div className="mx-auto flex w-full max-w-[1265px] items-start justify-center">
        <Sidebar me={me} unread={unread} />
        <main className="min-h-screen w-full min-w-0 max-w-[600px] flex-1 border-border pb-16 md:border-x md:pb-0">
          {children}
        </main>
        <RightPanel trends={trends} suggestions={suggestions} />
      </div>
      <MobileNav me={me} unread={unread} />
      <MobileCompose me={me} />
    </div>
  );
}
