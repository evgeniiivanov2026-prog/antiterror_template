export default function Loading() {
  return (
    <div className="space-y-px pt-16">
      {Array.from({ length: 5 }, (_, index) => (
        <div key={index} className="flex gap-3 border-b border-border px-4 py-4">
          <div className="size-10 animate-pulse rounded-full bg-white/10" />
          <div className="flex-1 space-y-2">
            <div className="h-4 w-36 animate-pulse rounded bg-white/10" />
            <div className="h-4 w-full animate-pulse rounded bg-white/10" />
            <div className="h-4 w-2/3 animate-pulse rounded bg-white/10" />
          </div>
        </div>
      ))}
    </div>
  );
}
