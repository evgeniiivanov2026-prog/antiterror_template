import { Suspense } from "react";
import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { ExploreView } from "@/components/search/explore-view";

export default async function ExplorePage({
  searchParams,
}: {
  searchParams: Promise<{ q?: string }>;
}) {
  const session = await auth();
  if (!session?.user) redirect("/login");
  const { q = "" } = await searchParams;

  return (
    <Suspense>
      <ExploreView user={session.user} initialQuery={q} />
    </Suspense>
  );
}
