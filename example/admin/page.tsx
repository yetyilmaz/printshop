"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { KanbanBoard } from "@/app/components/KanbanBoard";
import { Trash2 } from "lucide-react";
import { PortfolioManager } from "./PortfolioManager";

// Reuse the type or import it. Let's define it here to match previous style or import.
// Step 211 defined it locally. I will stick to that to minimize diffs, but add 'type' field handling.
type Order = {
  publicNumber: string;
  createdAt: string;
  status: string;
  paymentStatus: string;
  customer: { name: string; phone: string };
  calc: { total: number };
  params: { material: string; quality: string };
  // Add optional fields used in UI
  type: string;
  purpose?: string;
};

export default function AdminPage() {
  const [password, setPassword] = useState("");
  const [isAuth, setIsAuth] = useState(false);
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [tab, setTab] = useState<"orders" | "pricing" | "portfolio">("orders");
  const [view, setView] = useState<"table" | "kanban">("kanban");

  async function loadOrders() {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`/api/admin/orders?password=${encodeURIComponent(password)}`);
      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.error || "Ошибка загрузки заказов");
      }
      const data = await res.json();
      setOrders(data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  async function updateOrderStatus(publicNumber: string, status: string) {
    try {
      const res = await fetch("/api/admin/orders", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password, publicNumber, updates: { status } }),
      });
      if (!res.ok) throw new Error("Ошибка обновления");
      await loadOrders(); // Refresh
    } catch (err: any) {
      setError(err.message);
    }
  }

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`/api/admin/orders?password=${encodeURIComponent(password)}`);
      if (!res.ok) {
        throw new Error("Неверный пароль");
      }
      const data = await res.json();
      setOrders(data);
      setIsAuth(true);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  // If not authenticated, show login form
  if (!isAuth) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <form onSubmit={handleLogin} className="glass p-8 space-y-4 w-full max-w-sm">
          <h1 className="text-2xl font-bold text-center">Вход в админ-панель</h1>
          {error && <div className="text-red-600 text-sm bg-red-50 p-3 rounded-xl">{error}</div>}
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Пароль"
            className="input-field"
            required
          />
          <button
            type="submit"
            disabled={loading}
            className="btn btn-primary w-full"
          >
            {loading ? "Вход..." : "Войти"}
          </button>
        </form>
      </div>
    );
  }

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold tracking-tight">Админ-панель</h1>

        <div className="flex gap-2">
          {tab === "orders" && (
            <div className="bg-[rgba(10,10,10,0.05)] p-1 rounded-[14px] flex text-sm">
              <button
                onClick={() => setView("table")}
                className={`px-3 py-1 rounded-[10px] transition-all ${view === "table" ? "bg-white shadow-sm font-medium" : "text-[rgba(10,10,10,0.5)] hover:text-black"}`}
              >
                Таблица
              </button>
              <button
                onClick={() => setView("kanban")}
                className={`px-3 py-1 rounded-[10px] transition-all ${view === "kanban" ? "bg-white shadow-sm font-medium" : "text-[rgba(10,10,10,0.5)] hover:text-black"}`}
              >
                Канбан
              </button>
            </div>
          )}
          <button
            onClick={() => {
              setIsAuth(false);
              setPassword("");
            }}
            className="btn btn-ghost text-xs h-[32px]"
          >
            Выход
          </button>
        </div>
      </div>

      {/* Табы */}
      <div className="flex gap-4 border-b border-[rgba(10,10,10,0.08)]">
        <button
          onClick={() => setTab("orders")}
          className={`px-4 py-2 font-medium transition-colors border-b-2 ${tab === "orders" ? "border-black text-black" : "border-transparent text-[rgba(10,10,10,0.5)] hover:text-black"}`}
        >
          Заказы ({orders.length})
        </button>
        <button
          onClick={() => setTab("pricing")}
          className={`px-4 py-2 font-medium transition-colors border-b-2 ${tab === "pricing" ? "border-black text-black" : "border-transparent text-[rgba(10,10,10,0.5)] hover:text-black"}`}
        >
          Цены и параметры
        </button>
        <button
          onClick={() => setTab("portfolio")}
          className={`px-4 py-2 font-medium transition-colors border-b-2 ${tab === "portfolio" ? "border-black text-black" : "border-transparent text-[rgba(10,10,10,0.5)] hover:text-black"}`}
        >
          Портфолио
        </button>
      </div>

      {/* Таб Заказов */}
      {tab === "orders" && (
        <div>
          <div className="mb-4 flex gap-2 justify-between">
            <button
              onClick={() => loadOrders()}
              className="btn text-sm"
            >
              🔄 Обновить
            </button>
          </div>

          {error && <div className="text-red-600 mb-4 bg-red-50 p-3 rounded-[18px]">{error}</div>}

          {orders.length === 0 ? (
            <div className="text-[rgba(10,10,10,0.5)] text-center py-10">Заказы не найдены</div>
          ) : view === "kanban" ? (
            // @ts-ignore - passing compatible types
            <KanbanBoard orders={orders as any} onUpdateStatus={updateOrderStatus} />
          ) : (
            <div className="glass overflow-hidden rounded-[24px]">
              <div className="overflow-x-auto">
                <table className="w-full text-sm border-collapse">
                  <thead>
                    <tr className="bg-[rgba(10,10,10,0.03)] border-b border-[rgba(10,10,10,0.06)]">
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">№</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Клиент</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Материал</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Цена</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Статус</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Оплата</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Дата</th>
                      <th className="text-left p-4 font-medium text-[rgba(10,10,10,0.6)]">Действия</th>
                    </tr>
                  </thead>
                  <tbody>
                    {orders.map((order) => (
                      <tr key={order.publicNumber} className="border-b border-[rgba(10,10,10,0.06)] hover:bg-[rgba(10,10,10,0.02)] transition-colors">
                        <td className="p-4 font-mono text-xs">{order.publicNumber}</td>
                        <td className="p-4">
                          <div className="text-sm font-medium">{order.customer.name}</div>
                          <div className="text-xs text-[rgba(10,10,10,0.5)]">{order.customer.phone}</div>
                        </td>
                        <td className="p-4">
                          {(order as any).type === "MANUAL_REVIEW" ? (
                            <div className="text-xs">
                              <div className="font-semibold bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full inline-block mb-1">Ручная оценка</div>
                              <div className="text-[rgba(10,10,10,0.6)] truncate max-w-[150px]" title={(order as any).purpose}>{(order as any).purpose}</div>
                            </div>
                          ) : (
                            <span className="pill">{order.params?.material}</span>
                          )}
                        </td>
                        <td className="p-4 font-bold">{order.calc?.total ? `${order.calc.total} ₸` : "—"}</td>
                        <td className="p-4">
                          <span className="px-2 py-1 rounded-[8px] text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            {order.status}
                          </span>
                        </td>
                        <td className="p-4">
                          <span
                            className={`px-2 py-1 rounded-[8px] text-xs font-medium border ${order.paymentStatus === "PAID"
                              ? "bg-green-50 text-green-700 border-green-100"
                              : "bg-yellow-50 text-yellow-700 border-yellow-100"
                              }`}
                          >
                            {order.paymentStatus || ((order as any).type === "MANUAL_REVIEW" ? "Ожидает" : "—")}
                          </span>
                        </td>
                        <td className="p-4 text-xs text-[rgba(10,10,10,0.5)]">
                          {new Date(order.createdAt).toLocaleDateString("ru-RU")}
                        </td>
                        <td className="p-4">
                          <div className="flex gap-2">
                            <Link
                              href={`/admin/${order.publicNumber}`}
                              className="btn btn-ghost h-[32px] px-3 py-0 text-xs"
                            >
                              Открыть
                            </Link>
                            <button
                              onClick={async () => {
                                if (!confirm("Удалить этот заказ?")) return;
                                try {
                                  const res = await fetch("/api/admin/orders", {
                                    method: "DELETE",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                      password,
                                      publicNumber: order.publicNumber,
                                    }),
                                  });
                                  if (!res.ok) throw new Error("Ошибка");
                                  setError(null);
                                  await loadOrders();
                                } catch (err: any) {
                                  setError(err.message);
                                }
                              }}
                              className="btn h-[32px] px-3 py-0 text-xs text-red-600 hover:bg-red-50 border-transparent"
                            >
                              <Trash2 size={14} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Таб Цен */}
      {tab === "pricing" && (
        <PricingEditor password={password} />
      )}

      {/* Таб Портфолио */}
      {tab === "portfolio" && (
        <PortfolioManager password={password} />
      )}
    </div>
  );
}

function PricingEditor({ password }: { password: string }) {
  const [config, setConfig] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  async function loadConfig() {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`/api/admin/pricing?password=${encodeURIComponent(password)}`);
      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || "Ошибка загрузки");
      }
      setConfig(data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  // Auto-load on mount
  useEffect(() => {
    loadConfig();
  }, []);

  async function saveConfig() {
    setLoading(true);
    setError(null);
    setSuccess(false);
    try {
      const res = await fetch("/api/admin/pricing", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password, config }),
      });
      if (!res.ok) throw new Error("Ошибка сохранения");
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  if (!config) {
    return (
      <div className="flex flex-col items-center gap-4 py-10">
        {error ? (
          <>
            <div className="text-red-600 bg-red-50 p-4 rounded-xl">{error}</div>
            <button onClick={loadConfig} className="btn btn-primary">
              Повторить попытку
            </button>
          </>
        ) : (
          <div className="text-[rgba(10,10,10,0.5)]">Загрузка параметров...</div>
        )}
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {error && <div className="text-red-600 bg-red-50 p-4 rounded-xl">{error}</div>}
      {success && (
        <div className="text-green-600 bg-green-50 p-4 rounded-xl">Сохранено успешно!</div>
      )}

      {/* Материалы */}
      <div className="glass p-6">
        <h2 className="text-xl font-bold mb-4">Материалы</h2>
        <div className="space-y-4">
          {Object.entries(config.materials).map(([key, mat]: [string, any]) => (
            <div key={key} className="border border-[rgba(10,10,10,0.08)] bg-white/40 rounded-xl p-4 space-y-3">
              <div className="flex justify-between items-center">
                <h3 className="font-semibold text-sm bg-black text-white px-2 py-1 rounded-[6px]">{key}</h3>
                <button
                  onClick={async () => {
                    if (!confirm(`Удалить материал ${key}?`)) return;
                    try {
                      const res = await fetch("/api/admin/pricing", {
                        method: "PUT",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                          password,
                          action: "delete",
                          materialKey: key,
                        }),
                      });
                      if (!res.ok) throw new Error("Ошибка");
                      const data = await res.json();
                      setConfig(data.config);
                      setSuccess(true);
                      setTimeout(() => setSuccess(false), 3000);
                    } catch (err: any) {
                      setError(err.message);
                    }
                  }}
                  className="text-red-600 text-xs font-medium hover:underline"
                >
                  Удалить
                </button>
              </div>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                  <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Плотность (г/см³)</label>
                  <input
                    type="number"
                    step="0.01"
                    value={mat.density}
                    onChange={(e) => {
                      const newConfig = { ...config };
                      newConfig.materials[key].density = parseFloat(e.target.value);
                      setConfig(newConfig);
                    }}
                    className="input-field py-1 px-2 text-sm h-[34px]"
                  />
                </div>
                <div>
                  <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Цена за грамм (₸)</label>
                  <input
                    type="number"
                    step="1"
                    value={mat.pricePerGram}
                    onChange={(e) => {
                      const newConfig = { ...config };
                      newConfig.materials[key].pricePerGram = parseFloat(e.target.value);
                      setConfig(newConfig);
                    }}
                    className="input-field py-1 px-2 text-sm h-[34px]"
                  />
                </div>
                <div>
                  <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Цена за час (₸)</label>
                  <input
                    type="number"
                    step="100"
                    value={mat.pricePerHour}
                    onChange={(e) => {
                      const newConfig = { ...config };
                      newConfig.materials[key].pricePerHour = parseFloat(e.target.value);
                      setConfig(newConfig);
                    }}
                    className="input-field py-1 px-2 text-sm h-[34px]"
                  />
                </div>
                <div>
                  <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Стартовый сбор (₸)</label>
                  <input
                    type="number"
                    step="100"
                    value={mat.setupFee}
                    onChange={(e) => {
                      const newConfig = { ...config };
                      newConfig.materials[key].setupFee = parseFloat(e.target.value);
                      setConfig(newConfig);
                    }}
                    className="input-field py-1 px-2 text-sm h-[34px]"
                  />
                </div>
              </div>
            </div>
          ))}
          <AddMaterialForm config={config} setConfig={setConfig} password={password} setError={setError} setSuccess={setSuccess} />
        </div>
      </div>

      {/* Общие параметры */}
      <div className="glass p-6">
        <h2 className="text-xl font-bold mb-4">Общие параметры</h2>
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div>
            <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Мин. сумма заказа (₸)</label>
            <input
              type="number"
              step="100"
              value={config.other.minOrderPrice}
              onChange={(e) => {
                const newConfig = { ...config };
                newConfig.other.minOrderPrice = parseFloat(e.target.value);
                setConfig(newConfig);
              }}
              className="input-field"
            />
          </div>
          <div>
            <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Скидка (%)</label>
            <input
              type="number"
              step="1"
              min="0"
              max="100"
              value={Math.round(config.other.discountPercent * 100)}
              onChange={(e) => {
                const newConfig = { ...config };
                newConfig.other.discountPercent = parseFloat(e.target.value) / 100;
                setConfig(newConfig);
              }}
              className="input-field"
            />
          </div>
          <div>
            <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Порог скидки (кол-во)</label>
            <input
              type="number"
              value={config.other.discountThreshold.qty}
              onChange={(e) => {
                const newConfig = { ...config };
                newConfig.other.discountThreshold.qty = parseInt(e.target.value);
                setConfig(newConfig);
              }}
              className="input-field"
            />
          </div>
          <div>
            <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Порог скидки (сумма ₸)</label>
            <input
              type="number"
              step="1000"
              value={config.other.discountThreshold.sum}
              onChange={(e) => {
                const newConfig = { ...config };
                newConfig.other.discountThreshold.sum = parseFloat(e.target.value);
                setConfig(newConfig);
              }}
              className="input-field"
            />
          </div>
        </div>
      </div>

      <button
        onClick={saveConfig}
        disabled={loading}
        className="btn btn-primary w-full md:w-auto"
      >
        {loading ? "Сохранение..." : "Сохранить параметры"}
      </button>
    </div>
  );
}

function AddMaterialForm({
  config,
  setConfig,
  password,
  setError,
  setSuccess,
}: {
  config: any;
  setConfig: any;
  password: string;
  setError: any;
  setSuccess: any;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [newMaterial, setNewMaterial] = useState({
    name: "",
    density: 1.2,
    pricePerGram: 20,
    pricePerHour: 3500,
    setupFee: 1500,
    riskCoefficient: 1.0,
  });
  const [loading, setLoading] = useState(false);

  const handleAddMaterial = async () => {
    if (!newMaterial.name.trim()) {
      setError("Введите название материала");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch("/api/admin/pricing", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          password,
          action: "add",
          materialKey: newMaterial.name.toUpperCase(),
          materialData: {
            density: newMaterial.density,
            pricePerGram: newMaterial.pricePerGram,
            pricePerHour: newMaterial.pricePerHour,
            setupFee: newMaterial.setupFee,
            riskCoefficient: newMaterial.riskCoefficient,
          },
        }),
      });
      if (!res.ok) throw new Error("Ошибка");
      const data = await res.json();
      setConfig(data.config);
      setNewMaterial({
        name: "",
        density: 1.2,
        pricePerGram: 20,
        pricePerHour: 3500,
        setupFee: 1500,
        riskCoefficient: 1.0,
      });
      setIsOpen(false);
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) {
    return (
      <button
        onClick={() => setIsOpen(true)}
        className="w-full py-3 border-2 border-dashed border-[rgba(10,10,10,0.1)] rounded-[18px] text-[rgba(10,10,10,0.5)] hover:border-black hover:text-black transition-all text-sm font-medium"
      >
        + Добавить новый материал
      </button>
    );
  }

  return (
    <div className="glass p-5 space-y-4 ring-2 ring-blue-500/10">
      <div className="flex justify-between items-center">
        <h3 className="font-semibold text-sm">Новый материал</h3>
        <button
          onClick={() => setIsOpen(false)}
          className="text-[rgba(10,10,10,0.5)] hover:text-black text-xs"
        >
          Отмена
        </button>
      </div>

      <div className="grid grid-cols-2 gap-3 text-sm">
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Название материала *</label>
          <input
            type="text"
            value={newMaterial.name}
            onChange={(e) =>
              setNewMaterial({ ...newMaterial, name: e.target.value })
            }
            placeholder="PLA"
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Плотность</label>
          <input
            type="number"
            step="0.01"
            value={newMaterial.density}
            onChange={(e) =>
              setNewMaterial({
                ...newMaterial,
                density: parseFloat(e.target.value),
              })
            }
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Цена/г</label>
          <input
            type="number"
            step="1"
            value={newMaterial.pricePerGram}
            onChange={(e) =>
              setNewMaterial({
                ...newMaterial,
                pricePerGram: parseFloat(e.target.value),
              })
            }
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Цена/ч</label>
          <input
            type="number"
            step="100"
            value={newMaterial.pricePerHour}
            onChange={(e) =>
              setNewMaterial({
                ...newMaterial,
                pricePerHour: parseFloat(e.target.value),
              })
            }
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">Сбор</label>
          <input
            type="number"
            step="100"
            value={newMaterial.setupFee}
            onChange={(e) =>
              setNewMaterial({
                ...newMaterial,
                setupFee: parseFloat(e.target.value),
              })
            }
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
        <div>
          <label className="block text-[11px] text-[rgba(10,10,10,0.5)] mb-1">К-Риска</label>
          <input
            type="number"
            step="0.1"
            min="1"
            value={newMaterial.riskCoefficient}
            onChange={(e) =>
              setNewMaterial({
                ...newMaterial,
                riskCoefficient: parseFloat(e.target.value),
              })
            }
            className="input-field py-1 px-2 text-sm h-[34px]"
          />
        </div>
      </div>

      <button
        onClick={handleAddMaterial}
        disabled={loading}
        className="btn btn-primary w-full text-xs"
      >
        {loading ? "Добавление..." : "Добавить материал"}
      </button>
    </div>
  );
}
