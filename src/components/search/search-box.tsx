"use client";

import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";
import { Search } from "lucide-react";

export function SearchBox({ initialQuery = "" }: { initialQuery?: string }) {
  const router = useRouter();
  const [value, setValue] = useState(initialQuery);

  function onSubmit(event: FormEvent) {
    event.preventDefault();
    const query = value.trim();
    if (!query) return;
    router.push(`/explore?q=${encodeURIComponent(query)}`);
  }

  return (
    <form onSubmit={onSubmit} className="relative">
      <Search className="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
      <input
        value={value}
        onChange={(event) => setValue(event.target.value)}
        placeholder="Поиск"
        className="h-11 w-full rounded-full bg-zinc-900 pl-11 pr-4 text-sm outline-none placeholder:text-muted focus:bg-black focus:ring-1 focus:ring-accent"
      />
    </form>
  );
}
