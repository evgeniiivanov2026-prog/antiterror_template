export function CharacterRing({ value, max = 280 }: { value: number; max?: number }) {
  const remaining = max - value;
  const progress = Math.min(value / max, 1);
  const radius = 10;
  const circumference = 2 * Math.PI * radius;
  const color =
    remaining < 0 ? "#f4212e" : remaining <= 20 ? "#ffd400" : "#1d9bf0";

  return (
    <div className="relative h-6 w-6">
      <svg viewBox="0 0 24 24" className="h-6 w-6 -rotate-90">
        <circle
          cx="12"
          cy="12"
          r={radius}
          fill="none"
          stroke="#2f3336"
          strokeWidth="2"
        />
        <circle
          cx="12"
          cy="12"
          r={radius}
          fill="none"
          stroke={color}
          strokeWidth="2"
          strokeDasharray={`${circumference * progress} ${circumference}`}
          strokeLinecap="round"
        />
      </svg>
      {remaining <= 20 && (
        <span
          className="absolute inset-0 flex items-center justify-center text-[10px]"
          style={{ color }}
        >
          {remaining}
        </span>
      )}
    </div>
  );
}
