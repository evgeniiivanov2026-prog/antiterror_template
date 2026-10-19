export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <section className="hidden flex-col justify-between bg-gradient-to-br from-[#031018] via-black to-[#071a2b] p-12 lg:flex">
        <div className="flex size-14 items-center justify-center rounded-full bg-accent text-2xl font-black text-white">P</div>
        <div>
          <h1 className="max-w-md text-5xl font-black leading-tight">Короткие мысли. Живая лента.</h1>
          <p className="mt-4 max-w-md text-lg text-muted">
            Pulse — микроблог с подписками, ответами и темами. Тёмная тема, быстрые отметки и лента без лишнего шума.
          </p>
        </div>
        <p className="text-sm text-muted">Демо: ada@pulse.dev · пароль password123</p>
      </section>
      <section className="flex items-center justify-center px-6 py-12">{children}</section>
    </div>
  );
}
