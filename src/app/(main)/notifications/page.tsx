import { NotificationList } from "@/components/notifications/notification-list";

export default function NotificationsPage() {
  return (
    <div>
      <header className="sticky top-0 z-20 border-b border-zinc-800 bg-black/75 px-4 py-3 text-xl font-bold backdrop-blur-md">
        Уведомления
      </header>
      <NotificationList />
    </div>
  );
}
