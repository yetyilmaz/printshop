const materials = [
  { name: "PETG", use: "универсальный пластик для прочных деталей", limits: "не для очень высокой температуры" },
  { name: "ASA", use: "улица/UV, корпуса, автотематика", limits: "требует аккуратной печати" },
  { name: "PA (нейлон)", use: "нагрузка, износ, функциональные детали", limits: "гигроскопичен, сложнее печать" },
  { name: "CoPA", use: "прочность и ударка, функционал", limits: "цена выше, печать сложнее" },
];

export default function MaterialsPage() {
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Услуги и материалы</h1>
      <p className="text-gray-700">
        Подбор материала под задачу: улица/нагрузка/температура/точность.
      </p>

      <div className="grid gap-4 md:grid-cols-2">
        {materials.map((m) => (
          <div key={m.name} className="rounded-2xl border p-5">
            <div className="font-semibold text-lg">{m.name}</div>
            <div className="text-sm text-gray-700 mt-2">
              <div><span className="font-medium">Где применять:</span> {m.use}</div>
              <div className="mt-1"><span className="font-medium">Ограничения:</span> {m.limits}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}