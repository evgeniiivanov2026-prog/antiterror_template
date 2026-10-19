import { ColumnHeader } from "@/components/layout/column-header";
import { NotificationList } from "@/components/notifications/notification-list";
import { getNotifications } from "@/lib/notifications";
import { requireUserId } from "@/lib/session";

export const metadata = { title: "Уведомления" };

export default async function NotificationsPage() {
  const userId = await requireUserId();
  const initial = await getNotifications(userId);
  return (
    <>
      <ColumnHeader title="Уведомления" />
      <NotificationList initial={initial} />
    </>
  );
}
