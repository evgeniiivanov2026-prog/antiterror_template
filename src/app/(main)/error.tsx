"use client";

export default function MainError({ reset }: { error: Error; reset: () => void }) {
  return (
    <div className="px-8 py-16 text-center">
      <h1 className="text-xl font-bold">Не получилось открыть страницу</h1>
      <p className="mt-2 text-muted">Попробуйте ещё раз. Если ошибка останется, обновите вкладку.</p>
      <button type="button" onClick={reset} className="mt-5 font-bold text-accent">
        Повторить
      </button>
    </div>
  );
}
