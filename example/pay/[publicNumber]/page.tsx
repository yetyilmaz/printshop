export const runtime = "nodejs";
export const dynamic = "force-dynamic";

import { findOrderByPublicNumber } from "@/lib/ordersStore";

export default async function PayPage({
  params,
}: {
  params: Promise<{ publicNumber: string }>;
}) {
  const resolvedParams = await params;
  const publicNumber = resolvedParams.publicNumber;
  
  console.log("PayPage: searching for publicNumber:", publicNumber);
  
  const order = await findOrderByPublicNumber(publicNumber);
  
  console.log("PayPage: found order:", !!order, order?.publicNumber);

  if (!order) {
    return (
      <div className="p-6">
        <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
          Заказ не найден: {publicNumber}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Оплата заказа</h1>

      <div className="rounded-2xl border p-5 space-y-3">
        <div>
          <b>Номер заказа:</b> {order.publicNumber}
        </div>
        <div>
          <b>Сумма:</b> {order.calc.total} ₸
        </div>

        <div className="mt-4">
          <div className="font-semibold">Kaspi QR</div>
          <div className="mt-2 h-40 w-40 bg-gray-100 flex items-center justify-center rounded-xl">
            QR
          </div>
          <div className="text-sm text-gray-700 mt-1">
            Назначение платежа: {order.publicNumber}
          </div>
        </div>

        <div className="mt-4">
          <div className="font-semibold">Halyk QR</div>
          <div className="mt-2 h-40 w-40 bg-gray-100 flex items-center justify-center rounded-xl">
            QR
          </div>
        </div>

        <div className="text-sm text-gray-600 mt-4">
          После оплаты статус изменится на «Ожидает подтверждения» (сделаем на следующем шаге).
        </div>
      </div>
    </div>
  );
}