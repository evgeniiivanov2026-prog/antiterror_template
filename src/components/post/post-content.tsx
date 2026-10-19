import Link from "next/link";
import { safeHttpUrl } from "@/lib/utils";

const TOKEN = /(https?:\/\/[^\s<]+|@[a-zA-Z0-9_]{1,15}|#[\p{L}\p{N}_]+)/gu;

export function PostContent({ text, className }: { text: string; className?: string }) {
  const nodes: React.ReactNode[] = [];
  const matcher = new RegExp(TOKEN.source, TOKEN.flags);
  let last = 0;
  let match: RegExpExecArray | null;
  let index = 0;

  while ((match = matcher.exec(text))) {
    if (match.index > last) nodes.push(text.slice(last, match.index));
    const token = match[0];
    if (token.startsWith("@")) {
      nodes.push(
        <Link key={index} href={`/${token.slice(1).toLowerCase()}`} className="text-accent hover:underline">
          {token}
        </Link>,
      );
    } else if (token.startsWith("#")) {
      nodes.push(
        <Link
          key={index}
          href={`/search?q=${encodeURIComponent(token.toLocaleLowerCase("ru-RU"))}`}
          className="text-accent hover:underline"
        >
          {token}
        </Link>,
      );
    } else {
      const href = safeHttpUrl(token);
      nodes.push(
        href ? (
          <a key={index} href={href} target="_blank" rel="noreferrer noopener" className="text-accent hover:underline break-all">
            {token}
          </a>
        ) : (
          token
        ),
      );
    }
    last = match.index + token.length;
    index += 1;
  }

  if (last < text.length) nodes.push(text.slice(last));

  return (
    <p className={className ?? "whitespace-pre-wrap break-words text-[15px] leading-5 text-foreground"}>
      {nodes}
    </p>
  );
}
