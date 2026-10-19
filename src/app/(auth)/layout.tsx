export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <div className="hidden items-center justify-center bg-zinc-950 lg:flex">
        <div className="max-w-md px-10">
          <div className="mb-8 flex h-16 w-16 items-center justify-center rounded-full bg-white text-3xl font-black text-black">
            V
          </div>
          <h1 className="text-5xl font-black tracking-tight">Всё происходит здесь.</h1>
          <p className="mt-6 text-xl text-muted">
            Vox — живая лента коротких мыслей, ответов и трендов. Тёмная, быстрая и знакомая.
          </p>
        </div>
      </div>
      <div className="flex items-center justify-center px-6 py-12">{children}</div>
    </div>
  );
}
