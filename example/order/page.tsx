"use client";

import Link from "next/link";
import { useEffect, useMemo, useState, useRef } from "react";
import { createPortal } from "react-dom";
import { CheckCircle, Upload, Box, AlertOrWarning, FileQuestion, Image as ImageIcon, ChevronDown, Check, AlertCircle } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { Model3DViewer } from "@/app/components/Model3DViewer";

type UploadResult = {
  fileId: string;
  originalName: string;
  sizeBytes: number;
  storedPath: string;
  ext: string;
  bbox: { x: number; y: number; z: number };
  volumeMm3?: number;
  warnings: string[];
};

type CalcResult = {
  volumeMm3: number;
  gramsOne: number;
  hoursOne: number;
  discount: number;
  breakdown: {
    materialCost: number;
    machineCost: number;
    setupFee: number;
    supportsFee: number;
    subtotal: number;
  };
  total: number;
  note: string;
};

// --- Custom Select Component ---
function CustomSelect({
  value,
  onChange,
  options
}: {
  value: string;
  onChange: (val: string) => void;
  options: { label: string; value: string }[]
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [mounted, setMounted] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const [dropdownPos, setDropdownPos] = useState({ top: 0, left: 0, width: 0 });

  useEffect(() => {
    setMounted(true);
  }, []);

  // Close on click outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      const target = event.target as Node;
      const clickedButton = containerRef.current?.contains(target);
      const clickedDropdown = dropdownRef.current?.contains(target);
      
      if (!clickedButton && !clickedDropdown) {
        setIsOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Update dropdown position when opened
  useEffect(() => {
    if (isOpen && buttonRef.current) {
      const rect = buttonRef.current.getBoundingClientRect();
      setDropdownPos({
        top: rect.bottom + window.scrollY,
        left: rect.left + window.scrollX,
        width: rect.width
      });
    }
  }, [isOpen]);

  const selectedLabel = options.find((o) => o.value === value)?.label || value;

  return (
    <div className="relative" ref={containerRef}>
      <button
        ref={buttonRef}
        onClick={() => setIsOpen(!isOpen)}
        className="input-field text-left flex justify-between items-center"
      >
        <span>{selectedLabel}</span>
        <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform ${isOpen ? "rotate-180" : ""}`} />
      </button>

      {mounted && isOpen && createPortal(
        <motion.div
          ref={dropdownRef}
          initial={{ opacity: 0, y: 5 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: 5 }}
          transition={{ duration: 0.15 }}
          style={{
            position: 'fixed',
            top: `${dropdownPos.top + 8}px`,
            left: `${dropdownPos.left}px`,
            width: `${dropdownPos.width}px`
          }}
          className="z-[9999] bg-white border border-[rgba(10,10,10,0.1)] rounded-[18px] shadow-2xl overflow-hidden p-1 max-h-[250px] overflow-y-auto"
        >
          {options.map((opt) => (
            <button
              key={opt.value}
              onClick={() => {
                onChange(opt.value);
                setIsOpen(false);
              }}
              className={`w-full text-left px-3 py-2 rounded-[12px] text-[13px] flex items-center justify-between transition-colors ${value === opt.value
                ? "bg-black text-white font-medium shadow-sm"
                : "text-gray-700 hover:bg-black/5"
                }`}
            >
              {opt.label}
              {value === opt.value && <Check className="w-4 h-4" />}
            </button>
          ))}
        </motion.div>,
        document.body
      )}
    </div>
  );
}

// --- Функция форматирования телефона ---
function formatPhoneNumber(value: string): string {
  // Извлекаем только цифры
  const digits = value.replace(/\D/g, "");
  
  // Если пусто
  if (digits.length === 0) return "";
  
  // Убираем первую 7 если она есть (это код России)
  let phoneDigits = digits;
  if (phoneDigits.startsWith("7")) {
    phoneDigits = phoneDigits.slice(1);
  } else if (phoneDigits.startsWith("8")) {
    phoneDigits = phoneDigits.slice(1);
  }
  
  // Ограничиваем до 10 цифр (номер без кода страны)
  phoneDigits = phoneDigits.slice(0, 10);
  
  // Форматируем
  if (phoneDigits.length === 0) return "+7 (";
  if (phoneDigits.length <= 3) return `+7 (${phoneDigits}`;
  if (phoneDigits.length <= 6) return `+7 (${phoneDigits.slice(0, 3)}) ${phoneDigits.slice(3)}`;
  if (phoneDigits.length <= 8) return `+7 (${phoneDigits.slice(0, 3)}) ${phoneDigits.slice(3, 6)}-${phoneDigits.slice(6)}`;
  return `+7 (${phoneDigits.slice(0, 3)}) ${phoneDigits.slice(3, 6)}-${phoneDigits.slice(6, 8)}-${phoneDigits.slice(8, 10)}`;
}

// --- Компонент Toast ---
function Toast({ message, type }: { message: string; type: "error" | "success" | "warning" }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 20 }}
      className={`fixed bottom-4 right-4 max-w-xs px-4 py-3 rounded-[12px] flex items-center gap-2 text-sm font-medium shadow-lg z-50 ${
        type === "error" ? "bg-red-500 text-white" :
        type === "success" ? "bg-green-500 text-white" :
        "bg-yellow-500 text-white"
      }`}
    >
      {type === "error" && <AlertCircle size={18} />}
      {type === "success" && <CheckCircle size={18} />}
      {message}
    </motion.div>
  );
}

export default function OrderPage() {
  const [tab, setTab] = useState<"auto" | "manual">("auto");

  // Auto Form State
  const [file, setFile] = useState<File | null>(null);
  const [upload, setUpload] = useState<UploadResult | null>(null);
  const [calc, setCalc] = useState<CalcResult | null>(null);
  const [loadingUpload, setLoadingUpload] = useState(false);
  const [loadingCalc, setLoadingCalc] = useState(false);
  const [dragOver, setDragOver] = useState(false);

  // Manual Form State
  const [manualFiles, setManualFiles] = useState<File[]>([]);
  const [manualUploads, setManualUploads] = useState<UploadResult[]>([]);
  const [loadingManualUpload, setLoadingManualUpload] = useState(false);

  const [itemType, setItemType] = useState<string>("other");
  const [itemTypeCustom, setItemTypeCustom] = useState("");
  const [approxSize, setApproxSize] = useState<string>("");
  const [description, setDescription] = useState("");
  const [userPriority, setUserPriority] = useState<string>("unknown");

  // Common State
  const [error, setError] = useState<string | null>(null);
  const [toast, setToast] = useState<{ message: string; type: "error" | "success" | "warning" } | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [showModel3D, setShowModel3D] = useState(false);
  const [orderCreated, setOrderCreated] = useState<{ payUrl?: string; publicNumber: string; status: string } | null>(null);

  // Auto Params
  const [material, setMaterial] = useState<string>("PETG");
  const [quality, setQuality] = useState<"DRAFT" | "STANDARD" | "FINE">("STANDARD");
  const [width, setWidth] = useState(0); // unused but requested in ref? 
  const [infill, setInfill] = useState<15 | 25 | 40 | 60 | 100>(25);
  const [qty, setQty] = useState(1);
  const [supports, setSupports] = useState(true);
  const [rush, setRush] = useState(false);
  const [materials, setMaterials] = useState<string[]>([]);

  useEffect(() => {
    fetch("/api/materials")
      .then((res) => res.json())
      .then((data) => {
        setMaterials(data.materials);
        if (data.materials.length > 0 && !data.materials.includes(material)) {
          setMaterial(data.materials[0]);
        }
      })
      .catch(() => {
        setMaterials(["PETG", "ASA", "PA", "COPA"]);
      });
  }, []);

  // Toast auto-hide
  useEffect(() => {
    if (toast) {
      const timer = setTimeout(() => setToast(null), 4000);
      return () => clearTimeout(timer);
    }
  }, [toast]);

  // --- Auto Scenario Handlers ---

  async function handleUploadAuto(selected: File) {
    setError(null);
    setCalc(null);
    setUpload(null);
    setFile(selected);

    const ext = selected.name.toLowerCase().endsWith(".stl")
      ? ".stl"
      : selected.name.toLowerCase().endsWith(".3mf")
        ? ".3mf"
        : "";

    if (!ext) {
      setError("Разрешены только файлы .stl и .3mf");
      return;
    }

    setLoadingUpload(true);
    try {
      const fd = new FormData();
      fd.append("file", selected);

      const res = await fetch("/api/upload", { method: "POST", body: fd });
      const data = await res.json();

      if (!res.ok) throw new Error(data?.error || "Ошибка загрузки");

      setUpload(data as UploadResult);
    } catch (e: any) {
      setError(e?.message ?? "Ошибка загрузки");
    } finally {
      setLoadingUpload(false);
    }
  }

  async function handleCalc() {
    if (!upload) return;
    setError(null);
    setLoadingCalc(true);
    try {
      const res = await fetch("/api/calc", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          bbox: upload.bbox,
          volumeMm3: upload.volumeMm3,
          material,
          quality,
          infill,
          qty,
          supports,
          rush,
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || "Ошибка расчёта");

      setCalc(data as CalcResult);
    } catch (e: any) {
      setError(e?.message ?? "Ошибка расчёта");
    } finally {
      setLoadingCalc(false);
    }
  }

  useEffect(() => {
    if (upload && !loadingUpload) void handleCalc();
  }, [upload, material, quality, infill, qty, supports, rush]);


  // --- Manual Scenario Handlers ---

  async function handleUploadManual(files: FileList | null) {
    if (!files) return;
    const newFiles = Array.from(files);
    setManualFiles(prev => [...prev, ...newFiles]);

    setLoadingManualUpload(true);
    setError(null);
    try {
      const promises = newFiles.map(async (f) => {
        const fd = new FormData();
        fd.append("file", f);
        const res = await fetch("/api/upload", { method: "POST", body: fd });
        if (!res.ok) throw new Error("Ошибка загрузки файла " + f.name);
        return res.json();
      });

      const results = await Promise.all(promises);
      setManualUploads(prev => [...prev, ...results]);
    } catch (e: any) {
      setError(e.message || "Ошибка загрузки файлов");
    } finally {
      setLoadingManualUpload(false);
    }
  }


  // --- Submit Order ---

  async function handleCreateOrder() {
    const errors: Record<string, string> = {};

    if (!name.trim()) {
      errors.name = "Укажите имя";
    }
    if (!phone.trim()) {
      errors.phone = "Укажите телефон";
    } else if (phone.replace(/\D/g, "").length < 10) {
      errors.phone = "Неполный номер телефона";
    }

    if (tab === "auto") {
      if (!upload) {
        errors.upload = "Загрузите STL файл";
      }
      if (!calc) {
        errors.calc = "Дождитесь расчёта цены";
      }
    } else {
      if (!description.trim()) {
        errors.description = "Опишите задачу";
      }
    }

    setValidationErrors(errors);

    if (Object.keys(errors).length > 0) {
      setToast({
        message: "Пожалуйста, заполните все обязательные поля",
        type: "error"
      });
      return;
    }

    setError(null);

    const commonData = {
      customer: { name, phone },
    };

    let payload: any = {};

    if (tab === "auto") {
      payload = {
        ...commonData,
        type: "AUTO_CALC",
        upload,
        calc,
        params: { material, quality, infill, qty, supports, rush },
      };
    } else {
      payload = {
        ...commonData,
        type: "MANUAL_REVIEW",
        description,
        itemType: itemType === "other" ? itemTypeCustom : itemType,
        approxSize,
        userPriority,
        fileIds: manualUploads.map(u => u.fileId),
      };
    }

    try {
      const res = await fetch("/api/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || "Ошибка создания заказа");

      setOrderCreated({
        payUrl: data.payUrl,
        publicNumber: data.publicNumber,
        status: tab === "auto" ? "created" : "pending_review"
      });
      
      setToast({
        message: "Заказ успешно создан!",
        type: "success"
      });
    } catch (e: any) {
      const errorMsg = e?.message ?? "Ошибка создания заказа";
      setError(errorMsg);
      setToast({
        message: errorMsg,
        type: "error"
      });
    }
  }

  // --- Render ---

  return (
    <div className="grid md:grid-cols-2 gap-[14px] items-start">

      {/* Left Column: Input Form */}
      <motion.section layout className="glass p-[24px] flex flex-col gap-2 overflow-hidden">
        {/* Scenario Switcher */}
        <div className="flex bg-[rgba(10,10,10,0.04)] p-1 rounded-[14px] relative isolate">
          <button
            onClick={() => setTab("auto")}
            className={`flex-1 py-2 rounded-[10px] text-[13px] font-medium transition-colors relative z-10 ${tab === "auto" ? "text-black" : "text-[rgba(10,10,10,0.5)] hover:text-black"}`}
          >
            У меня есть 3D-модель (STL)
            {tab === "auto" && (
              <motion.div
                layoutId="tab-bg"
                className="absolute inset-0 bg-white shadow-sm rounded-[10px] -z-10"
                transition={{ type: "spring", bounce: 0.2, duration: 0.6 }}
              />
            )}
          </button>
          <button
            onClick={() => setTab("manual")}
            className={`flex-1 py-2 rounded-[10px] text-[13px] font-medium transition-colors relative z-10 ${tab === "manual" ? "text-black" : "text-[rgba(10,10,10,0.5)] hover:text-black"}`}
          >
            Нет модели / Нужна помощь
            {tab === "manual" && (
              <motion.div
                layoutId="tab-bg"
                className="absolute inset-0 bg-white shadow-sm rounded-[10px] -z-10"
                transition={{ type: "spring", bounce: 0.2, duration: 0.6 }}
              />
            )}
          </button>
        </div>

        <div className="pt-[18px] pb-[12px] flex flex-col gap-[12px]">

          {/* Client Info (Common) */}
          <div className="flex gap-2">
            <div className="flex-1">
              <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[4px]">Имя</div>
              <input 
                className={`input-field ${validationErrors.name ? "border-red-400 bg-red-50" : ""}`}
                value={name} 
                onChange={e => {
                  setName(e.target.value);
                  if (validationErrors.name) {
                    const newErrors = { ...validationErrors };
                    delete newErrors.name;
                    setValidationErrors(newErrors);
                  }
                }}
                placeholder="Иван" 
              />
              {validationErrors.name && (
                <div className="text-[11px] text-red-600 mt-1 flex items-center gap-1">
                  <AlertCircle size={12} /> {validationErrors.name}
                </div>
              )}
            </div>
            <div className="flex-1">
              <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[4px]">Телефон</div>
              <input 
                className={`input-field ${validationErrors.phone ? "border-red-400 bg-red-50" : ""}`}
                value={phone} 
                onChange={e => {
                  const formatted = formatPhoneNumber(e.target.value);
                  setPhone(formatted);
                  if (validationErrors.phone) {
                    const newErrors = { ...validationErrors };
                    delete newErrors.phone;
                    setValidationErrors(newErrors);
                  }
                }}
                onKeyDown={e => {
                  // Разрешаем удаление
                  if (e.key === "Backspace") {
                    if (phone.length <= 4) { // "+7 (" = 4 символа
                      e.preventDefault();
                      setPhone("");
                    }
                  }
                }}
                placeholder="+7 (___) ___-__-__" 
                type="tel" 
              />
              {validationErrors.phone && (
                <div className="text-[11px] text-red-600 mt-1 flex items-center gap-1">
                  <AlertCircle size={12} /> {validationErrors.phone}
                </div>
              )}
            </div>
          </div>

          <AnimatePresence mode="wait">
            {tab === "auto" ? (
              /* AUTO SCENARIO FORM */
              <motion.div
                key="auto"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.2 }}
                className="flex flex-col gap-[12px]"
              >
                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">STL файл</div>
                  <div
                    className={`border border-dashed rounded-[22px] p-[14px] bg-[rgba(255,255,255,0.60)] flex justify-between items-center gap-[12px] transition-all ${
                      validationErrors.upload ? "border-red-400 bg-red-50" : `border-[rgba(10,10,10,0.18)] ${dragOver ? "bg-[rgba(10,10,10,0.03)] border-[rgba(10,10,10,0.28)]" : ""}`
                    }`}
                    onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                    onDragLeave={(e) => { e.preventDefault(); setDragOver(false); }}
                    onDrop={(e) => {
                      e.preventDefault();
                      setDragOver(false);
                      const f = e.dataTransfer.files?.[0];
                      if (f) void handleUploadAuto(f);
                    }}
                  >
                    <div>
                      <div className="font-[650] text-[14px] tracking-[-0.02em] truncate max-w-[200px]">
                        {file ? file.name : "Перетащи STL сюда"}
                      </div>
                      <div className="text-[12px] text-[rgba(10,10,10,0.45)]">
                        {upload ? `${Math.round(upload.sizeBytes / 1024)} KB • ${upload.volumeMm3 ? Math.round(upload.volumeMm3 / 1000) + ' см³' : 'Ok'}` : "или нажми для выбора"}
                      </div>
                    </div>
                    {upload && (
                      <button 
                        onClick={() => setShowModel3D(true)}
                        className="btn bg-blue-600 hover:bg-blue-700 text-black px-3 py-2 text-[12px] font-semibold"
                        title="Показать 3D модель"
                      >
                        3D
                      </button>
                    )}
                    {!upload && (
                      <button onClick={() => document.getElementById("fileInputAuto")?.click()} className="btn">
                        <Upload size={16} />
                      </button>
                    )}
                    <input
                      id="fileInputAuto"
                      type="file"
                      accept=".stl,.3mf"
                      className="hidden"
                      onChange={(e) => {
                        const f = e.target.files?.[0];
                        if (f) void handleUploadAuto(f);
                      }}
                    />
                  </div>
                  {validationErrors.upload && (
                    <div className="text-[11px] text-red-600 mt-1 flex items-center gap-1">
                      <AlertCircle size={12} /> {validationErrors.upload}
                    </div>
                  )}
                </div>

                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Материал</div>
                  <CustomSelect
                    value={material}
                    onChange={setMaterial}
                    options={materials.map(m => ({ label: m, value: m }))}
                  />
                </div>

                <div className="flex gap-2">
                  <div className="field flex-1">
                    <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Качество</div>
                    <CustomSelect
                      value={quality}
                      onChange={(v) => setQuality(v as any)}
                      options={[
                        { label: "Draft", value: "DRAFT" },
                        { label: "Standard", value: "STANDARD" },
                        { label: "Fine", value: "FINE" },
                      ]}
                    />
                  </div>
                  <div className="field w-[80px]">
                    <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Кол-во</div>
                    <input className="input-field" type="number" min="1" value={qty} onChange={e => setQty(Number(e.target.value))} />
                  </div>
                </div>
              </motion.div>
            ) : (
              /* MANUAL SCENARIO FORM */
              <motion.div
                key="manual"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.2 }}
                className="flex flex-col gap-[12px]"
              >
                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Тип изделия</div>
                  <div className="flex flex-wrap gap-2">
                    {["Запчасть", "Корпус", "Крепление", "Декор", "other"].map(t => (
                      <button
                        key={t}
                        onClick={() => setItemType(t)}
                        className={`inline-flex items-center gap-2 font-medium text-[12px] border rounded-full px-3 py-2 cursor-pointer transition-all duration-200 ${
                          itemType === t 
                            ? "!bg-black !text-white !border-black shadow-md scale-105" 
                            : "bg-white/55 text-gray-700 border-[rgba(10,10,10,0.1)] hover:bg-black/10 hover:border-black/20 hover:scale-105 active:scale-95"
                        }`}
                      >
                        {t === "other" ? "Другое" : t}
                      </button>
                    ))}
                  </div>
                  {itemType === "other" && (
                    <input
                      className="input-field mt-2"
                      placeholder="Укажите что это..."
                      value={itemTypeCustom}
                      onChange={e => setItemTypeCustom(e.target.value)}
                    />
                  )}
                </div>

                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Примерный размер</div>
                  <div className="flex gap-2">
                    {["С ладонь", "С телефон", "Больше"].map(s => (
                      <button
                        key={s}
                        onClick={() => setApproxSize(s)}
                        className={`flex-1 py-2 text-[12px] rounded-[14px] border transition-all duration-200 cursor-pointer ${
                          approxSize === s 
                            ? "bg-black text-white border-black shadow-md scale-105" 
                            : "bg-white/50 border-[rgba(10,10,10,0.1)] hover:bg-black/10 hover:border-black/20 hover:scale-105 active:scale-95"
                        }`}
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Что нужно сделать? (ТЗ)</div>
                  <textarea
                    className={`input-field min-h-[100px] resize-none ${validationErrors.description ? "border-red-400 bg-red-50" : ""}`}
                    placeholder="Опишите, для чего деталь, какие будут нагрузки, важен ли внешний вид..."
                    value={description}
                    onChange={e => {
                      setDescription(e.target.value);
                      if (validationErrors.description) {
                        const newErrors = { ...validationErrors };
                        delete newErrors.description;
                        setValidationErrors(newErrors);
                      }
                    }}
                  />
                  {validationErrors.description && (
                    <div className="text-[11px] text-red-600 mt-1 flex items-center gap-1">
                      <AlertCircle size={12} /> {validationErrors.description}
                    </div>
                  )}
                </div>

                <div className="field">
                  <div className="text-[12px] text-[rgba(10,10,10,0.62)] mb-[6px]">Примеры / Чертежи (опционально)</div>
                  <div className="flex flex-wrap gap-2">
                    {manualUploads.map(u => (
                      <div key={u.fileId} className="relative w-16 h-16 rounded-[12px] bg-white border flex items-center justify-center overflow-hidden">
                        {/* Simple Extension/Icon */}
                        <div className="text-[9px] text-gray-500">{u.ext}</div>
                      </div>
                    ))}
                    <label className="w-16 h-16 rounded-[12px] border border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-black/5 transition-all">
                      <Upload size={16} className="opacity-40" />
                      <input
                        type="file"
                        multiple
                        accept=".jpg,.png,.pdf,.jpeg"
                        className="hidden"
                        onChange={e => void handleUploadManual(e.target.files)}
                      />
                    </label>
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>

          {error && <div className="text-[13px] text-red-600 bg-red-50 p-2 rounded-[12px]">{error}</div>}

        </div>
      </motion.section>

      {/* Right Column: Calculations & Submit */}
      <motion.aside layout className="glass p-[18px] h-full overflow-hidden">
        {orderCreated ? (
          <div className="flex flex-col items-center justify-center h-full py-10 animate-in fade-in zoom-in duration-300">
            <div className="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4 border border-green-200 shadow-sm">
              <CheckCircle className="w-8 h-8 text-green-600" />
            </div>

            {tab === "auto" ? (
              <>
                <div className="text-xl font-bold mb-2">Заказ принят!</div>
                <p className="text-center text-gray-500 mb-6 text-sm max-w-[250px]">
                  Номер заказа: <span className="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded">{orderCreated.publicNumber}</span>
                </p>
                <div className="p-4 bg-green-50 text-green-800 text-sm rounded-xl border border-green-100 text-center">
                  Скоро менеджер подтвердит заказ и пришлёт ссылку на оплату.
                </div>
              </>
            ) : (
              <>
                <div className="text-xl font-bold mb-2">Заявка отправлена!</div>
                <p className="text-center text-gray-500 mb-6 text-sm max-w-[250px]">
                  Номер заявки: <span className="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded">{orderCreated.publicNumber}</span>
                </p>
                <div className="p-4 bg-blue-50 text-blue-800 text-sm rounded-xl border border-blue-100 text-center">
                  Мы изучим ТЗ и свяжемся с вами для согласования цены и деталей.
                </div>
              </>
            )}

            <button onClick={() => window.location.reload()} className="btn mt-6">
              Новый заказ
            </button>
          </div>
        ) : (
          <AnimatePresence mode="wait">
            {tab === "auto" ? (
              /* AUTO SUMMARY */
              <motion.div
                key="auto-summary"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                transition={{ duration: 0.2 }}
                className="h-full flex flex-col justify-between"
              >
                <div>
                  <div className="flex justify-between items-end gap-[10px] flex-wrap mb-4">
                    <div>
                      <div className="font-[650] text-[14px]">Параметры и расчёт</div>
                      <div className="text-[12px] text-[rgba(10,10,10,0.45)] mt-[4px]">
                        Автоматический расчёт
                      </div>
                    </div>
                    <span className="pill">
                      {loadingCalc ? "Считаю..." : calc ? "Расчёт готов" : "Ожидаю STL..."}
                    </span>
                  </div>

                  <div className="mt-[16px] glass-card p-[14px] rounded-[22px]">
                    <div className="flex justify-between gap-[10px]">
                      <div className="text-[12px] text-[rgba(10,10,10,0.62)]">Оценка веса / времени</div>
                      <div className="font-[650] text-[12px]">
                        {calc ? `${Math.ceil(calc.gramsOne * qty)} г • ${calc.hoursOne} ч` : "—"}
                      </div>
                    </div>
                    <div className="flex justify-between gap-[10px] mt-[8px]">
                      <div className="text-[12px] text-[rgba(10,10,10,0.62)]">Скидка</div>
                      <div className="font-[650] text-[12px]">
                        {calc && calc.discount > 0 ? `${Math.round(calc.discount * 100)}%` : "—"}
                      </div>
                    </div>
                    <div className="flex justify-between gap-[10px] mt-[10px] pt-[10px] border-t border-[rgba(10,10,10,0.08)]">
                      <div className="text-[12px] text-[rgba(10,10,10,0.62)]">Итого</div>
                      <div className="font-[700] text-[14px]">
                        {calc ? `${calc.total} ₸` : "—"}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="mt-[16px]">
                  <button
                    onClick={() => void handleCreateOrder()}
                    disabled={!calc || !!error || !phone || !!validationErrors.phone}
                    className="btn btn-primary w-full h-[48px] text-[15px] btn-glow disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Рассчитать цену и оформить заказ
                  </button>
                </div>
              </motion.div>
            ) : (
              /* MANUAL SUMMARY */
              <motion.div
                key="manual-summary"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                transition={{ duration: 0.2 }}
                className="flex flex-col h-full justify-between"
              >
                <div>
                  <h3 className="font-[650] text-[16px] mb-2">Ручная Оценка</h3>
                  <p className="text-[13px] text-[rgba(10,10,10,0.6)] mb-4">
                    Идеально, если у вас нет 3D-модели или нужна консультация инженера.
                  </p>

                  <div className="pill inline-flex mb-4">
                    <FileQuestion size={14} className="mr-2" />
                    Бесплатная консультация
                  </div>
                </div>

                <div>
                  <div className="glass-card p-[14px] rounded-[22px] mb-4 bg-blue-50/50 border-blue-100">
                    <div className="flex items-start gap-3">
                      <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center text-xl shadow-sm">💡</div>
                      <div className="text-[12px] text-blue-900/80">
                        Опишите задачу максимально подробно. Мы подберём технологию и материал под ваш бюджет.
                      </div>
                    </div>
                  </div>

                  <button
                    onClick={() => void handleCreateOrder()}
                    disabled={!description || !phone}
                    className="btn btn-primary w-full h-[48px] text-[15px] btn-glow disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Отправить на оценку
                  </button>
                  <div className="text-center text-[11px] text-[rgba(10,10,10,0.4)] mt-2">
                    Мы свяжемся с вами и предложим оптимальный вариант
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        )}
      </motion.aside>

      {/* 3D Model Preview Modal */}
      <AnimatePresence>
        {showModel3D && upload && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4"
            onClick={() => setShowModel3D(false)}
          >
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
              className="bg-white rounded-[20px] w-full max-w-2xl h-[600px] flex flex-col shadow-2xl"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 className="text-xl font-bold">3D Просмотр - {upload.originalName}</h2>
                <button
                  onClick={() => setShowModel3D(false)}
                  className="text-gray-400 hover:text-gray-600 text-2xl"
                >
                  ×
                </button>
              </div>
              <div className="flex-1 overflow-hidden">
                {typeof window !== "undefined" && (
                  <Model3DViewer modelPath={upload.storedPath} />
                )}
              </div>
              <div className="p-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                <button
                  onClick={() => setShowModel3D(false)}
                  className="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 font-medium"
                >
                  Закрыть
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Toast Notifications */}
      <AnimatePresence>
        {toast && (
          <Toast message={toast.message} type={toast.type} />
        )}
      </AnimatePresence>
    </div>
  );
}