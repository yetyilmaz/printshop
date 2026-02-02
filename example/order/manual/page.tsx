"use client";

import { useState } from "react";
import Link from "next/link";

export default function ManualOrderPage() {
  const [files, setFiles] = useState<File[]>([]);
  const [dragOver, setDragOver] = useState(false);
  const [purpose, setPurpose] = useState("");
  const [deadline, setDeadline] = useState("");
  const [description, setDescription] = useState("");
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<{ publicNumber: string } | null>(null);

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(true);
  };

  const handleDragLeave = () => {
    setDragOver(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setDragOver(false);
    const droppedFiles = Array.from(e.dataTransfer.files);
    setFiles((prev) => [...prev, ...droppedFiles]);
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const selectedFiles = Array.from(e.target.files);
      setFiles((prev) => [...prev, ...selectedFiles]);
    }
  };

  const removeFile = (index: number) => {
    setFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);

    if (!phone.trim()) {
      setError("Телефон обязателен");
      return;
    }

    if (files.length === 0) {
      setError("Загрузите хотя бы один файл");
      return;
    }

    setLoading(true);

    try {
      // Upload files first
      const formData = new FormData();
      files.forEach((file) => {
        formData.append("files", file);
      });

      const uploadRes = await fetch("/api/upload/files", {
        method: "POST",
        body: formData,
      });

      if (!uploadRes.ok) {
        throw new Error("Ошибка загрузки файлов");
      }

      const uploadData = await uploadRes.json();
      console.log("Upload response:", uploadData);

      // Create manual order
      const orderRes = await fetch("/api/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          type: "MANUAL_REVIEW",
          purpose,
          deadline,
          description,
          name: name || "Аноним",
          phone,
          fileIds: uploadData.fileIds,
        }),
      });

      console.log("Order response status:", orderRes.status);
      const orderData = await orderRes.json();
      console.log("Order response data:", orderData);

      if (!orderRes.ok) {
        throw new Error(orderData.error || "Ошибка создания заказа");
      }
      setSuccess({ publicNumber: orderData.publicNumber });

      // Reset form
      setFiles([]);
      setPurpose("");
      setDeadline("");
      setDescription("");
      setName("");
      setPhone("");
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "Произошла ошибка при отправке"
      );
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-semibold">Спасибо за заказ!</h1>

        <div className="rounded-2xl border border-green-200 bg-green-50 p-6">
          <div className="text-green-800 space-y-2">
            <p className="font-semibold">Ваш заказ принят</p>
            <p>Номер заказа: <code className="bg-green-100 px-2 py-1 rounded">{success.publicNumber}</code></p>
            <p>Мы свяжемся с вами по телефону {phone} для обсуждения деталей и стоимости.</p>
          </div>
        </div>

        <Link
          href={`/order/${success.publicNumber}`}
          className="inline-block rounded-xl bg-black px-5 py-3 text-white"
        >
          Посмотреть заказ
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Заказ без STL (ручная обработка)</h1>

      <div className="rounded-2xl border p-6 space-y-4">
        <div className="text-sm text-gray-700">
          Загрузите фото/чертежи/описание — админ оценит вручную, статус будет <b>MANUAL_REVIEW</b>.
        </div>

        {error && (
          <div className="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          {/* File Upload */}
          <div
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
            onClick={() => document.getElementById("file-input")?.click()}
            className={`rounded-xl border-2 border-dashed p-6 text-center cursor-pointer transition ${
              dragOver
                ? "border-black bg-gray-50"
                : "border-gray-300 hover:border-gray-400"
            }`}
          >
            <input
              id="file-input"
              type="file"
              multiple
              onChange={handleFileSelect}
              className="hidden"
              accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
            />
            <p className="text-sm text-gray-600">
              Перетащите файлы сюда или нажмите для выбора
            </p>
            <p className="text-xs text-gray-400 mt-1">
              Поддерживаются: изображения, PDF, документы Excel
            </p>
          </div>

          {files.length > 0 && (
            <div className="space-y-2">
              <p className="text-sm font-medium">Загруженные файлы ({files.length}):</p>
              <div className="space-y-1">
                {files.map((file, idx) => (
                  <div key={idx} className="flex items-center justify-between bg-gray-50 p-2 rounded text-sm">
                    <span className="truncate">{file.name}</span>
                    <button
                      type="button"
                      onClick={() => removeFile(idx)}
                      className="text-red-600 hover:text-red-800"
                    >
                      ✕
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Purpose */}
          <div>
            <label className="text-sm font-medium">Назначение детали</label>
            <input
              type="text"
              value={purpose}
              onChange={(e) => setPurpose(e.target.value)}
              className="mt-1 w-full rounded-xl border p-2"
              placeholder="для чего деталь"
            />
          </div>

          {/* Deadline */}
          <div>
            <label className="text-sm font-medium">Срок</label>
            <input
              type="text"
              value={deadline}
              onChange={(e) => setDeadline(e.target.value)}
              className="mt-1 w-full rounded-xl border p-2"
              placeholder="когда нужно (например, завтра, неделю, 20 января)"
            />
          </div>

          {/* Description */}
          <div>
            <label className="text-sm font-medium">Описание / условия эксплуатации</label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="mt-1 w-full rounded-xl border p-2"
              rows={4}
              placeholder="нагрузка, улица/температура, габариты, любые другие требования..."
            />
          </div>

          {/* Contact Info */}
          <div className="grid gap-3 md:grid-cols-2">
            <div>
              <label className="text-sm font-medium">Имя</label>
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="mt-1 w-full rounded-xl border p-2"
              />
            </div>
            <div>
              <label className="text-sm font-medium">Телефон *</label>
              <input
                type="tel"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                className="mt-1 w-full rounded-xl border p-2"
                placeholder="+7..."
                required
              />
            </div>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-xl bg-black py-3 text-white font-medium hover:bg-gray-800 disabled:bg-gray-400"
          >
            {loading ? "Отправка..." : "Отправить на оценку"}
          </button>
        </form>
      </div>
    </div>
  );
}