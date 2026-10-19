export function extractHashtags(content: string) {
  const matches = content.match(/#[\p{L}\p{N}_]+/gu) ?? [];
  return [
    ...new Set(
      matches.map((tag) => tag.slice(1).toLowerCase()).filter(Boolean),
    ),
  ];
}

export function extractMentions(content: string) {
  const matches = content.match(/@([a-zA-Z0-9_]+)/g) ?? [];
  return [...new Set(matches.map((tag) => tag.slice(1).toLowerCase()))];
}
