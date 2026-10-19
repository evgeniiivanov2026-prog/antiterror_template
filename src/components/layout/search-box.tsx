"use client";

import { Search } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";

export function SearchBox({ initial = "" }: { initial?: string }) {
  const router = useRouter();
  const [value, setValue] = useState(initial);

  return (
    <form
      className="sticky top-0 bg-black py-1"
      onSubmit={(event) => {
        event.preventDefault();
        const query = value.trim();
        router.push(query ? `/search?q=${encodeURIComponent(query)}` : "/search");
      }}
    >
      <label className="flex items-center gap-3 rounded-full bg-[#202327] px-4 py-3 text-muted focus-within:bg-black focus-within:ring-1 focus-within:ring-accent">
        <Search className="size-5" />
        <input
          value={value}
          onChange={(event) => setValue(event.target.value)}
          placeholder="Поиск"
          aria-label="Поиск"
          className="w-full bg-transparent text-[15px] text-foreground outline-none placeholder:text-muted"
        />
      </label>
    </form>
  );
}
