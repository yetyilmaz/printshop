import { motion, AnimatePresence } from "framer-motion";
import type { Order } from "@/app/types";

type KanbanProps = {
    orders: Order[];
    onUpdateStatus: (publicNumber: string, status: string) => Promise<void>;
};

const STATUSES = [
    { id: "WAITING_PAYMENT", label: "Ожидает оплаты", color: "bg-yellow-100 text-yellow-800" },
    { id: "PAID", label: "Оплачено", color: "bg-green-100 text-green-800" },
    { id: "IN_PROGRESS", label: "В работе", color: "bg-blue-100 text-blue-800" },
    { id: "PENDING_REVIEW", label: "На оценке", color: "bg-purple-100 text-purple-800" },
    { id: "COMPLETED", label: "Готово", color: "bg-gray-100 text-gray-800" },
];

export function KanbanBoard({ orders, onUpdateStatus }: KanbanProps) {
    const columns = STATUSES.map((status) => ({
        ...status,
        orders: orders.filter((o) => o.status === status.id),
    }));

    const unknownOrders = orders.filter(
        (o) => !STATUSES.find((s) => s.id === o.status)
    );
    if (unknownOrders.length > 0) {
        columns.push({ id: "UNKNOWN", label: "Неизвестно", color: "bg-red-100", orders: unknownOrders });
    }

    return (
        <div className="flex gap-4 overflow-x-auto pb-4 h-[calc(100vh-200px)] min-w-full">
            {columns.map((col) => (
                <div key={col.id} className="min-w-[320px] w-[320px] flex flex-col bg-gray-50/50 backdrop-blur-sm border border-gray-100 rounded-2xl max-h-full">
                    {/* Header */}
                    <div className={`p-4 rounded-t-2xl font-semibold flex justify-between items-center bg-white/50 backdrop-blur shadow-sm z-10 ${col.color.replace("text-", "border-b border-").replace("bg-", "border-")}`}>
                        <span>{col.label}</span>
                        <span className="text-xs font-bold bg-white px-2 py-1 rounded-full shadow-sm">{col.orders.length}</span>
                    </div>

                    {/* Cards */}
                    <div className="flex-1 overflow-y-auto p-3 space-y-3">
                        <AnimatePresence mode="popLayout">
                            {col.orders.map((order) => (
                                <motion.div
                                    layout
                                    initial={{ opacity: 0, scale: 0.9 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    exit={{ opacity: 0, scale: 0.9 }}
                                    transition={{ type: "spring", stiffness: 300, damping: 25 }}
                                    key={order.publicNumber}
                                    className="bg-white/80 backdrop-blur p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
                                >
                                    <div className="flex justify-between items-start mb-2">
                                        <span className="font-mono text-xs text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">{order.publicNumber}</span>
                                        <span className="text-xs text-gray-400">{new Date(order.createdAt).toLocaleDateString()}</span>
                                    </div>

                                    <h4 className="font-medium text-sm mb-1">{order.customer?.name || "Без имени"}</h4>
                                    <div className="text-xs text-gray-500 mb-3">{order.customer?.phone}</div>

                                    {order.type === "AUTO_CALC" ? (
                                        <div className="text-xs mb-3 p-2 bg-gray-50 rounded-lg flex justify-between items-center">
                                            <span className="font-semibold text-gray-900">{order.calc?.total} ₸</span>
                                            <span className="text-gray-500 max-w-[120px] truncate" title={order.params?.material}>{order.params?.material}</span>
                                        </div>
                                    ) : (
                                        <div className="text-xs mb-3 p-2 bg-purple-50 text-purple-700 rounded-lg">
                                            Ручной расчёт
                                        </div>
                                    )}

                                    <div className="pt-2 border-t border-gray-100">
                                        <select
                                            className="text-xs border border-gray-200 rounded-lg p-1.5 w-full bg-white hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all cursor-pointer"
                                            value=""
                                            onChange={(e) => {
                                                if (e.target.value) onUpdateStatus(order.publicNumber, e.target.value);
                                            }}
                                        >
                                            <option value="">Изменить статус...</option>
                                            {STATUSES.map(s => (
                                                <option key={s.id} value={s.id}>{s.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </div>
                </div>
            ))}
        </div>
    );
}
