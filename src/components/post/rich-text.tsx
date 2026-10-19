import Link from "next/link";

export function RichText({ content }: { content: string }) {
  const parts = content.split(/([#@][\p{L}\p{N}_]+)/u);

  return (
    <p className="whitespace-pre-wrap break-words text-[15px] leading-5 text-foreground">
      {parts.map((part, index) => {
        if (part.startsWith("#")) {
          return (
            <Link
              key={`${part}-${index}`}
              href={`/explore?q=${encodeURIComponent(part)}`}
              className="text-accent hover:underline"
            >
              {part}
            </Link>
          );
        }
        if (part.startsWith("@")) {
          return (
            <Link
              key={`${part}-${index}`}
              href={`/${part.slice(1)}`}
              className="text-accent hover:underline"
            >
              {part}
            </Link>
          );
        }
        return <span key={`${part}-${index}`}>{part}</span>;
      })}
    </p>
  );
}
