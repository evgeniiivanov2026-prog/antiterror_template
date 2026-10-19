"use client";

import { useState } from "react";
import { Sidebar } from "@/components/layout/sidebar";
import { RightSidebar } from "@/components/layout/right-sidebar";
import { MobileTabBar } from "@/components/layout/mobile-tab-bar";
import { ComposeDialog } from "@/components/layout/compose-dialog";
import type { SessionUser, SuggestionUser, TrendingTopic } from "@/types";

export function AppShell({
  user,
  unreadCount,
  topics,
  suggestions,
  children,
}: {
  user: SessionUser;
  unreadCount: number;
  topics: TrendingTopic[];
  suggestions: SuggestionUser[];
  children: React.ReactNode;
}) {
  const [composeOpen, setComposeOpen] = useState(false);

  return (
    <div className="mx-auto flex min-h-screen w-full max-w-[1280px] justify-center">
      <Sidebar user={user} unreadCount={unreadCount} onCompose={() => setComposeOpen(true)} />
      <main className="min-h-screen w-full max-w-[600px] border-x border-zinc-800 pb-16 md:pb-0">
        {children}
      </main>
      <RightSidebar topics={topics} suggestions={suggestions} />
      <MobileTabBar username={user.username} unreadCount={unreadCount} />
      <button
        type="button"
        onClick={() => setComposeOpen(true)}
        className="fixed bottom-20 right-4 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-2xl font-bold shadow-lg md:hidden"
        aria-label="Опубликовать"
      >
        +
      </button>
      <ComposeDialog open={composeOpen} onOpenChange={setComposeOpen} user={user} />
    </div>
  );
}
