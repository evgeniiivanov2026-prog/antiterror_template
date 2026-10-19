"use client";

import { useEffect, useRef } from "react";

export function LoadMore({
  hasMore,
  loading,
  error = false,
  onLoad,
}: {
  hasMore: boolean;
  loading: boolean;
  error?: boolean;
  onLoad: () => void;
}) {
  const ref = useRef<HTMLDivElement>(null);
  const onLoadRef = useRef(onLoad);
  const loadingRef = useRef(loading);
  onLoadRef.current = onLoad;
  loadingRef.current = loading;

  useEffect(() => {
    const element = ref.current;
    if (!element || !hasMore) return;
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((entry) => entry.isIntersecting) && !loadingRef.current) {
          onLoadRef.current();
        }
      },
      { rootMargin: "640px" },
    );
    observer.observe(element);
    return () => observer.disconnect();
  }, [hasMore, loading]);

  return (
    <div ref={ref} className="flex min-h-16 items-center justify-center text-sm text-muted">
      {error ? (
        <button type="button" className="text-accent hover:underline" onClick={onLoad}>
          Не удалось загрузить. Повторить
        </button>
      ) : loading ? (
        "Загрузка…"
      ) : null}
    </div>
  );
}
