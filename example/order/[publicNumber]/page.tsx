export const runtime = "nodejs";
export const dynamic = "force-dynamic";

import { findOrderByPublicNumber } from "@/lib/ordersStore";

export default async function OrderStatusPage({
  params,
}: {
  params: Promise<{ publicNumber: string }>;
}) {
  const resolvedParams = await params;
  const order = await findOrderByPublicNumber(resolvedParams.publicNumber);

  if (!order) {
    return <div>Заказ не найден</div>;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Статус заказа</h1>

      <div className="rounded-2xl border p-5 space-y-2">
        <div><b>Номер:</b> {order.publicNumber}</div>
        <div><b>Статус:</b> {order.status}</div>
        <div><b>Сумма:</b> {order.calc.total} ₸</div>
      </div>

      <div className="text-sm text-gray-600">
        История статусов:
        <ul className="list-disc ml-5 mt-1">
          {order.history.map((h: any, i: number) => (
            <li key={i}>
              {new Date(h.at).toLocaleString()} — {h.status}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}