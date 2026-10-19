"use client";

import { useEffect, useState } from "react";
import { formatTime } from "@/lib/format";

export function RelativeTime({ iso }: { iso: string }) {
  const [label, setLabel] = useState(() => formatTime(iso));

  useEffect(() => {
    setLabel(formatTime(iso));
    const timer = window.setInterval(() => setLabel(formatTime(iso)), 30_000);
    return () => window.clearInterval(timer);
  }, [iso]);

  return (
    <time dateTime={iso} title={new Date(iso).toLocaleString("ru-RU")} suppressHydrationWarning>
      {label}
    </time>
  );
}
