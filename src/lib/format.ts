export function formatTime(iso: string, now = Date.now()) {
  const date = new Date(iso);
  const diffSeconds = Math.max(0, Math.round((now - date.getTime()) / 1000));
  if (diffSeconds < 60) return `${diffSeconds}с`;
  const minutes = Math.round(diffSeconds / 60);
  if (minutes < 60) return `${minutes}м`;
  const hours = Math.round(minutes / 60);
  if (hours < 24) return `${hours}ч`;
  const days = Math.round(hours / 24);
  if (days < 7) return `${days}д`;
  return new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "short",
  }).format(date);
}

export function formatJoined(iso: string) {
  return new Intl.DateTimeFormat("ru-RU", {
    month: "long",
    year: "numeric",
  }).format(new Date(iso));
}

export function snippet(content: string, max = 90) {
  const flat = content.replace(/\s+/g, " ").trim();
  if (flat.length <= max) return flat;
  return `${flat.slice(0, max - 1).trimEnd()}…`;
}
