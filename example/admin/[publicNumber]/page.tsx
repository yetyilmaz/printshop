"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

export default function AdminOrderPage({
  params,
}: {
  params: Promise<{ publicNumber: string }>;
}) {
  const [publicNumber, setPublicNumber] = useState("");
  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [password, setPassword] = useState("");
  const [isAuth, setIsAuth] = useState(false);
  const router = useRouter();

  useEffect(() => {
    params.then((p) => setPublicNumber(p.publicNumber));
  }, [params]);

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const res = await fetch(
        `/api/admin/orders?password=${encodeURIComponent(password)}`
      );
      if (!res.ok) throw new Error("Неверный пароль");
      setIsAuth(true);
      await loadOrder();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  async function loadOrder() {
    if (!publicNumber) return;
    setLoading(true);
    try {
      const res = await fetch(
        `/api/orders?publicNumber=${encodeURIComponent(publicNumber)}`
      );
      if (!res.ok) throw new Error("Заказ не найден");
      const data = await res.json();
      setOrder(data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    if (isAuth && publicNumber) {
      loadOrder();
    }
  }, [isAuth, publicNumber]);

  if (!isAuth) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <Link href="/admin" className="text-blue-600 underline mb-4 inline-block">
          ← Вернуться
        </Link>
        <div className="rounded-2xl border p-6">
          <h1 className="text-2xl font-semibold mb-4">Вход</h1>
          <form onSubmit={handleLogin} className="space-y-4">
            <div>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Пароль"
                className="w-full border rounded-xl p-2"
                disabled={loading}
              />
            </div>
            {error && <div className="text-red-600 text-sm">{error}</div>}
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-black text-white rounded-xl py-2 disabled:bg-gray-400"
            >
              {loading ? "Проверка..." : "Войти"}
            </button>
          </form>
        </div>
      </div>
    );
  }

  if (!order) {
    return (
      <div className="space-y-4">
        <Link href="/admin" className="text-blue-600 underline">
          ← Вернуться
        </Link>
        <div className="text-gray-600">{loading ? "Загрузка..." : error || "Заказ не найден"}</div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <Link href="/admin" className="text-blue-600 underline">
          ← Вернуться
        </Link>
        <div className="flex gap-2">
          <h1 className="text-2xl font-semibold">Заказ {order.publicNumber}</h1>
          <button
            onClick={async () => {
              if (!confirm("Удалить этот заказ? Это действие необратимо.")) return;
              try {
                const res = await fetch("/api/admin/orders", {
                  method: "DELETE",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({
                    password,
                    publicNumber: order.publicNumber,
                  }),
                });
                if (!res.ok) throw new Error("Ошибка удаления");
                router.push("/admin");
              } catch (err: any) {
                alert(err.message);
              }
            }}
            className="px-3 py-1 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700"
          >
            Удалить
          </button>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-6">
        {/* Основная информация */}
        <div className="rounded-2xl border p-6">
          <h2 className="text-lg font-semibold mb-4">Информация</h2>
          <div className="space-y-2 text-sm">
            <div>
              <span className="text-gray-600">Номер:</span> {order.publicNumber}
            </div>
            <div>
              <span className="text-gray-600">Статус:</span>{" "}
              <span className="font-semibold">{order.status}</span>
            </div>
            <div>
              <span className="text-gray-600">Оплата:</span>{" "}
              <span className="font-semibold">{order.paymentStatus}</span>
            </div>
            <div>
              <span className="text-gray-600">Дата:</span>{" "}
              {new Date(order.createdAt).toLocaleString("ru-RU")}
            </div>
          </div>
        </div>

        {/* Клиент */}
        <div className="rounded-2xl border p-6">
          <h2 className="text-lg font-semibold mb-4">Клиент</h2>
          <div className="space-y-2 text-sm">
            <div>
              <span className="text-gray-600">Имя:</span> {order.customer.name}
            </div>
            <div>
              <span className="text-gray-600">Телефон:</span> {order.customer.phone}
            </div>
          </div>
        </div>

        {/* Параметры печати или описание заказа */}
        {(order as any).type === "MANUAL_REVIEW" ? (
          <div className="rounded-2xl border p-6">
            <h2 className="text-lg font-semibold mb-4">Информация о заказе</h2>
            <div className="space-y-2 text-sm">
              <div>
                <span className="text-gray-600">Тип:</span> Ручная оценка
              </div>
              <div>
                <span className="text-gray-600">Назначение:</span> {(order as any).purpose}
              </div>
              {(order as any).deadline && (
                <div>
                  <span className="text-gray-600">Срок:</span> {(order as any).deadline}
                </div>
              )}
              {(order as any).description && (
                <div>
                  <span className="text-gray-600">Описание:</span>
                  <p className="mt-1 p-2 bg-gray-50 rounded">{(order as any).description}</p>
                </div>
              )}
            </div>
          </div>
        ) : (
          <div className="rounded-2xl border p-6">
            <h2 className="text-lg font-semibold mb-4">Параметры печати</h2>
            <div className="space-y-2 text-sm">
              <div>
                <span className="text-gray-600">Материал:</span> {order.params?.material}
              </div>
              <div>
                <span className="text-gray-600">Качество:</span> {order.params?.quality}
              </div>
              <div>
                <span className="text-gray-600">Заполнение:</span> {order.params?.infill}%
              </div>
              <div>
                <span className="text-gray-600">Кол-во:</span> {order.params?.qty}
              </div>
              <div>
                <span className="text-gray-600">Поддержки:</span> {order.params?.supports ? "Да" : "Нет"}
              </div>
              <div>
                <span className="text-gray-600">Срочность:</span> {order.params?.rush ? "Срочно" : "Обычно"}
              </div>
            </div>
          </div>
        )}

        {/* Расчет - только для AUTO_CALC */}
        {(order as any).type !== "MANUAL_REVIEW" && order.calc && (
          <div className="rounded-2xl border p-6">
            <h2 className="text-lg font-semibold mb-4">Расчет стоимости</h2>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-600">Объём:</span> {order.calc.volumeMm3.toFixed(0)} mm³
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Вес:</span> {order.calc.gramsOne} г
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Время:</span> {order.calc.hoursOne.toFixed(1)} ч
              </div>
              <div className="flex justify-between border-t pt-2">
                <span className="text-gray-600">Материал:</span> {order.calc.breakdown.materialCost} ₸
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Машина:</span> {order.calc.breakdown.machineCost} ₸
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Сбор:</span> {order.calc.breakdown.setupFee} ₸
              </div>
              {order.calc.breakdown.supportsFee > 0 && (
                <div className="flex justify-between">
                  <span className="text-gray-600">Поддержки:</span> {order.calc.breakdown.supportsFee} ₸
                </div>
              )}
              <div className="flex justify-between border-t pt-2">
                <span className="text-gray-600">Подитог:</span> {order.calc.breakdown.subtotal} ₸
              </div>
              {order.calc.discount > 0 && (
                <div className="flex justify-between text-green-600">
                  <span>Скидка:</span> -{Math.round(order.calc.discount * 100)}%
                </div>
              )}
              <div className="flex justify-between border-t pt-2 font-semibold text-lg">
                <span>Итого:</span> {order.calc.total} ₸
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Загруженный файл или файлы */}
      {(order as any).type === "MANUAL_REVIEW" ? (
        (order as any).fileIds && (order as any).fileIds.length > 0 && (
          <div className="rounded-2xl border p-6">
            <h2 className="text-lg font-semibold mb-4">Загруженные файлы</h2>
            <div className="space-y-2 text-sm">
              {(order as any).fileIds.map((fileId: string, i: number) => (
                <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                  <span>{fileId}</span>
                  <a
                    href={`/uploads/${fileId}`}
                    download
                    className="px-3 py-1 bg-black text-white rounded text-xs"
                  >
                    Скачать
                  </a>
                </div>
              ))}
            </div>
          </div>
        )
      ) : (
        <div className="rounded-2xl border p-6">
          <h2 className="text-lg font-semibold mb-4">Файл</h2>
          <div className="space-y-2 text-sm">
            <div>
              <span className="text-gray-600">Имя:</span> {order.upload?.originalName}
            </div>
            <div>
              <span className="text-gray-600">Путь:</span> {order.upload?.storedPath}
            </div>
            <div>
              <span className="text-gray-600">Размер:</span> {order.upload ? (order.upload.sizeBytes / 1024).toFixed(0) : "—"} KB
            </div>
            {order.upload && (
              <div className="mt-4">
                <a
                  href={`/${order.upload.storedPath}`}
                  download
                  className="px-4 py-2 bg-black text-white rounded-xl text-sm inline-block"
                >
                  Скачать файл
                </a>
              </div>
            )}
          </div>
        </div>
      )}

      {/* История */}
      <div className="rounded-2xl border p-6">
        <h2 className="text-lg font-semibold mb-4">История статусов</h2>
        <div className="space-y-2">
          {order.history?.map((h: any, i: number) => (
            <div key={i} className="flex gap-4 text-sm py-2 border-b last:border-b-0">
              <div className="text-gray-600">
                {new Date(h.at).toLocaleString("ru-RU")}
              </div>
              <div className="font-semibold">{h.status}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Заметки админа */}
      <div className="rounded-2xl border p-6">
        <h2 className="text-lg font-semibold mb-4">Заметки админа</h2>
        <textarea
          value={order.adminNotes || ""}
          onChange={(e) => setOrder({ ...order, adminNotes: e.target.value })}
          className="w-full border rounded-xl p-3 min-h-24"
          placeholder="Ваши заметки..."
        />
        <button
          onClick={async () => {
            try {
              const res = await fetch("/api/admin/orders", {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                  password,
                  publicNumber: order.publicNumber,
                  updates: { adminNotes: order.adminNotes },
                }),
              });
              if (!res.ok) throw new Error("Ошибка");
              alert("Заметки сохранены");
            } catch (err: any) {
              alert(err.message);
            }
          }}
          className="mt-2 px-4 py-2 bg-black text-white rounded-xl text-sm"
        >
          Сохранить заметки
        </button>
      </div>

      {/* Управление статусом */}
      <div className="rounded-2xl border p-6">
        <h2 className="text-lg font-semibold mb-4">Управление</h2>
        <div className="space-y-3">
          <div>
            <label className="block text-sm font-medium mb-2">Статус заказа</label>
            <select
              value={order.status}
              onChange={(e) => setOrder({ ...order, status: e.target.value })}
              className="border rounded-xl p-2 w-full"
            >
              <option value="NEW">NEW</option>
              <option value="WAITING_PAYMENT">WAITING_PAYMENT</option>
              <option value="PAID">PAID</option>
              <option value="IN_QUEUE">IN_QUEUE</option>
              <option value="PRINTING">PRINTING</option>
              <option value="POSTPROCESS">POSTPROCESS</option>
              <option value="READY">READY</option>
              <option value="DELIVERED">DELIVERED</option>
              <option value="CANCELLED">CANCELLED</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">Статус оплаты</label>
            <select
              value={order.paymentStatus}
              onChange={(e) => setOrder({ ...order, paymentStatus: e.target.value })}
              className="border rounded-xl p-2 w-full"
            >
              <option value="WAITING_PAYMENT">WAITING_PAYMENT</option>
              <option value="PAID">PAID</option>
              <option value="REFUNDED">REFUNDED</option>
            </select>
          </div>

          <button
            onClick={async () => {
              try {
                const res = await fetch("/api/admin/orders", {
                  method: "PATCH",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({
                    password,
                    publicNumber: order.publicNumber,
                    updates: {
                      status: order.status,
                      paymentStatus: order.paymentStatus,
                    },
                  }),
                });
                if (!res.ok) throw new Error("Ошибка");
                alert("Статусы обновлены");
                await loadOrder();
              } catch (err: any) {
                alert(err.message);
              }
            }}
            className="w-full px-4 py-2 bg-black text-white rounded-xl"
          >
            Сохранить изменения
          </button>
        </div>
      </div>
    </div>
  );
}
