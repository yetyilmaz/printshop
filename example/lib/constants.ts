/**
 * Constants and configuration
 */

// Пароли и ключи
export const CONFIG = {
  ADMIN_PASSWORD: process.env.ADMIN_PASSWORD || "password123",
};

// Статусы
export const ORDER_STATUS = {
  WAITING_PAYMENT: "WAITING_PAYMENT",
  PAID: "PAID",
  IN_PROGRESS: "IN_PROGRESS",
  COMPLETED: "COMPLETED",
  PENDING_REVIEW: "PENDING_REVIEW",
} as const;

export const PAYMENT_STATUS = {
  WAITING_PAYMENT: "WAITING_PAYMENT",
  PAID: "PAID",
  PENDING_REVIEW: "Ожидание оценки",
} as const;

// Категории портфолио
export const PORTFOLIO_CATEGORIES = {
  podium: "Подиум",
  figurine: "Фигурки",
  prototype: "Прототипы",
  custom: "На заказ",
} as const;

// Параметры печати
export const PRINT_PARAMS = {
  qualities: [
    { value: "high", label: "Высокое" },
    { value: "medium", label: "Среднее" },
    { value: "draft", label: "Черновик" },
  ],
  infills: [20, 30, 50, 100],
  supports: [
    { value: "true", label: "Да" },
    { value: "false", label: "Нет" },
  ],
} as const;

// Routes
export const ROUTES = {
  HOME: "/",
  PORTFOLIO: "/portfolio",
  ORDER: "/order",
  ORDER_MANUAL: "/order/manual",
  ORDER_DETAIL: (publicNumber: string) => `/order/${publicNumber}`,
  PAY: (publicNumber: string) => `/pay/${publicNumber}`,
  ADMIN: "/admin",
  ADMIN_DETAIL: (publicNumber: string) => `/admin/${publicNumber}`,
  ADMIN_PORTFOLIO: "/admin/portfolio",
  MATERIALS: "/materials",
} as const;

// API Routes
export const API_ROUTES = {
  ORDERS: "/api/orders",
  ORDERS_DETAIL: (publicNumber: string) => `/api/orders?publicNumber=${publicNumber}`,
  CALC: "/api/calc",
  MATERIALS: "/api/materials",
  UPLOAD_FILES: "/api/upload/files",
  UPLOAD_GLB: "/api/upload/glb",
  ADMIN_ORDERS: "/api/admin/orders",
  ADMIN_PORTFOLIO: "/api/admin/portfolio",
  ADMIN_PRICING: "/api/admin/pricing",
} as const;

// Toast/Notification messages
export const MESSAGES = {
  // Success
  ORDER_CREATED: "Заказ успешно создан",
  PORTFOLIO_UPDATED: "Портфолио обновлено",
  CATEGORY_ADDED: "Раздел добавлен",
  PROJECT_SAVED: "Проект сохранён",
  FILE_UPLOADED: "Файл загружен",
  SETTINGS_SAVED: "Настройки сохранены",

  // Errors
  INVALID_PASSWORD: "Неверный пароль",
  ORDER_NOT_FOUND: "Заказ не найден",
  UPLOAD_ERROR: "Ошибка загрузки файлов",
  API_ERROR: "Ошибка сервера",
  VALIDATION_ERROR: "Ошибка валидации",
  REQUIRED_FIELD: "Это поле обязательно",
  PHONE_REQUIRED: "Телефон обязателен",
  FILES_REQUIRED: "Загрузите хотя бы один файл",
  
  // Info
  LOADING: "Загрузка...",
  SAVING: "Сохранение...",
  NO_DATA: "Данные не найдены",
} as const;
