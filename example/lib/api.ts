/**
 * API utilities for common operations
 */

import { API_ROUTES, MESSAGES } from "./constants";

export interface FetchOptions extends RequestInit {
  signal?: AbortSignal;
}

/**
 * Generic API fetch wrapper
 */
export async function apiFetch<T>(
  url: string,
  options?: FetchOptions,
  errorContext?: string
): Promise<T> {
  try {
    const response = await fetch(url, options);

    if (!response.ok) {
      const error = await response.json().catch(() => ({}));
      throw new Error(error.error || `HTTP ${response.status}`);
    }

    return await response.json();
  } catch (error: any) {
    const message = error.message || MESSAGES.API_ERROR;
    if (errorContext) {
      console.error(`[${errorContext}]`, error);
    }
    throw new Error(message);
  }
}

/**
 * POST request with JSON body
 */
export async function apiPost<T>(
  url: string,
  data: Record<string, any>,
  errorContext?: string
): Promise<T> {
  return apiFetch<T>(
    url,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    },
    errorContext
  );
}

/**
 * GET request
 */
export async function apiGet<T>(url: string, errorContext?: string): Promise<T> {
  return apiFetch<T>(url, { method: "GET" }, errorContext);
}

/**
 * DELETE request
 */
export async function apiDelete<T>(url: string, errorContext?: string): Promise<T> {
  return apiFetch<T>(url, { method: "DELETE" }, errorContext);
}

/**
 * Upload files to server
 */
export async function uploadFiles(files: File[]): Promise<{ fileIds: string[] }> {
  const formData = new FormData();
  files.forEach((file) => {
    formData.append("files", file);
  });

  return apiFetch<{ fileIds: string[] }>(
    API_ROUTES.UPLOAD_FILES,
    {
      method: "POST",
      body: formData,
    },
    "FileUpload"
  );
}

/**
 * Fetch order by public number
 */
export async function fetchOrder(publicNumber: string) {
  return apiFetch(
    API_ROUTES.ORDERS_DETAIL(publicNumber),
    { method: "GET" },
    "FetchOrder"
  );
}

/**
 * Create a new order
 */
export async function createOrder(data: Record<string, any>) {
  return apiPost(API_ROUTES.ORDERS, data, "CreateOrder");
}

/**
 * Fetch available materials
 */
export async function fetchMaterials() {
  return apiGet(API_ROUTES.MATERIALS, "FetchMaterials");
}

/**
 * Calculate order cost
 */
export async function calculateOrder(data: Record<string, any>) {
  return apiPost(API_ROUTES.CALC, data, "CalculateOrder");
}

/**
 * Fetch portfolio with authentication
 */
export async function fetchPortfolio(password?: string) {
  const url = password
    ? `${API_ROUTES.ADMIN_PORTFOLIO}?password=${encodeURIComponent(password)}`
    : API_ROUTES.ADMIN_PORTFOLIO;

  return apiFetch(url, { method: "GET" }, "FetchPortfolio");
}

/**
 * Update portfolio (admin)
 */
export async function updatePortfolio(data: Record<string, any>) {
  return apiPost(API_ROUTES.ADMIN_PORTFOLIO, data, "UpdatePortfolio");
}

/**
 * Fetch pricing config (admin)
 */
export async function fetchPricing(password: string) {
  return apiFetch(
    `${API_ROUTES.ADMIN_PRICING}?password=${encodeURIComponent(password)}`,
    { method: "GET" },
    "FetchPricing"
  );
}

/**
 * Update pricing (admin)
 */
export async function updatePricing(data: Record<string, any>) {
  return apiPost(API_ROUTES.ADMIN_PRICING, data, "UpdatePricing");
}

/**
 * Fetch orders (admin)
 */
export async function fetchAdminOrders(password: string) {
  return apiFetch(
    `${API_ROUTES.ADMIN_ORDERS}?password=${encodeURIComponent(password)}`,
    { method: "GET" },
    "FetchAdminOrders"
  );
}

/**
 * Handle API errors with user-friendly messages
 */
export function getErrorMessage(error: unknown, defaultMessage: string = MESSAGES.API_ERROR): string {
  if (error instanceof Error) {
    return error.message;
  }
  if (typeof error === "string") {
    return error;
  }
  return defaultMessage;
}

/**
 * Validate form data
 */
export function validateRequired(value: any, fieldName: string): string | null {
  if (!value || (typeof value === "string" && !value.trim())) {
    return `${fieldName} обязательно`;
  }
  return null;
}

export function validateEmail(email: string): string | null {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    return "Неверный email";
  }
  return null;
}

export function validatePhone(phone: string): string | null {
  const phoneRegex = /^[\d\s\-\+\(\)]+$/;
  if (!phoneRegex.test(phone) || phone.replace(/\D/g, "").length < 10) {
    return "Неверный номер телефона";
  }
  return null;
}

export function validateURL(url: string): string | null {
  try {
    new URL(url);
    return null;
  } catch {
    return "Неверный URL";
  }
}
