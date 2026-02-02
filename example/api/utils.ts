/**
 * API Server utilities
 * Общие функции для обработки API запросов
 */

import { NextResponse } from "next/server";
import fs from "fs/promises";
import path from "path";

// ============= Authentication =============

const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || "admin123";

export function verifyAdminPassword(password?: string): boolean {
  return password === ADMIN_PASSWORD;
}

export function getPasswordFromURL(url: string): string {
  return new URL(url).searchParams.get("password") || "";
}

export function checkAdminAuth(url: string): { valid: boolean; error?: NextResponse } {
  const password = getPasswordFromURL(url);
  if (!verifyAdminPassword(password)) {
    return {
      valid: false,
      error: NextResponse.json({ error: "Неверный пароль" }, { status: 401 }),
    };
  }
  return { valid: true };
}

// ============= File Operations =============

const ORDERS_FILE = path.join(process.cwd(), "orders.json");
const PRICES_FILE = path.join(process.cwd(), "prices.json");
const PORTFOLIO_FILE = path.join(process.cwd(), "portfolio.json");

export async function readFile<T>(filePath: string, defaultValue: T): Promise<T> {
  try {
    const data = await fs.readFile(filePath, "utf-8");
    return JSON.parse(data);
  } catch (e) {
    return defaultValue;
  }
}

export async function writeFile<T>(filePath: string, data: T): Promise<void> {
  await fs.writeFile(filePath, JSON.stringify(data, null, 2));
}

export async function readOrders() {
  return readFile(ORDERS_FILE, []);
}

export async function writeOrders(orders: any[]) {
  return writeFile(ORDERS_FILE, orders);
}

export async function readPrices() {
  return readFile(PRICES_FILE, {});
}

export async function writePrices(prices: any) {
  return writeFile(PRICES_FILE, prices);
}

export async function readPortfolio() {
  return readFile(PORTFOLIO_FILE, { projects: [], categories: [] });
}

export async function writePortfolio(portfolio: any) {
  return writeFile(PORTFOLIO_FILE, portfolio);
}

// ============= Response Helpers =============

export function successResponse<T>(data: T, status: number = 200) {
  return NextResponse.json(data, { status });
}

export function errorResponse(message: string, status: number = 400) {
  return NextResponse.json({ error: message }, { status });
}

export function unauthorizedResponse() {
  return NextResponse.json({ error: "Неверный пароль" }, { status: 401 });
}

export function notFoundResponse() {
  return NextResponse.json({ error: "Не найдено" }, { status: 404 });
}

// ============= Error Handling =============

export async function handleApiError(error: any, context?: string) {
  const message = error?.message || "Ошибка сервера";
  if (context) {
    console.error(`[${context}]`, error);
  } else {
    console.error(error);
  }
  return errorResponse(message, 500);
}

// ============= Request Body Parsing =============

export async function parseJSON<T>(req: Request): Promise<T> {
  try {
    return await req.json();
  } catch (error) {
    throw new Error("Неверный JSON в теле запроса");
  }
}

// ============= Validation =============

export function validateRequired(value: any, fieldName: string): string | null {
  if (!value || (typeof value === "string" && !value.trim())) {
    return `${fieldName} обязательно`;
  }
  return null;
}

export function validateEmail(email: string): string | null {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email) ? null : "Неверный email";
}

export function validatePhone(phone: string): string | null {
  const phoneRegex = /^[\d\s\-\+\(\)]+$/;
  const digitsOnly = phone.replace(/\D/g, "");
  return phoneRegex.test(phone) && digitsOnly.length >= 10
    ? null
    : "Неверный номер телефона";
}

export function validateURL(url: string): string | null {
  try {
    new URL(url);
    return null;
  } catch {
    return "Неверный URL";
  }
}

// ============= Data Transformations =============

export function generatePublicNumber(prefix: string = "ORDER"): string {
  const num = Math.floor(10000 + Math.random() * 90000);
  return `${prefix}-${num}`;
}

export function ensureUniquePublicNumber(
  publicNumber: string,
  existingNumbers: string[]
): string {
  let unique = publicNumber;
  while (existingNumbers.includes(unique)) {
    unique = generatePublicNumber();
  }
  return unique;
}

export function getHistoryEntry(status: string, note?: string) {
  return {
    at: new Date().toISOString(),
    status,
    note,
  };
}

// ============= Constants =============

export const DEFAULT_MATERIALS = {
  PETG: { density: 1.27, pricePerGram: 12, ratePerHour: 2500, setupFee: 1000 },
  ASA: { density: 1.07, pricePerGram: 16, ratePerHour: 3000, setupFee: 1500 },
  PA: { density: 1.14, pricePerGram: 25, ratePerHour: 3500, setupFee: 2000 },
  COPA: { density: 1.14, pricePerGram: 30, ratePerHour: 3800, setupFee: 2000 },
} as const;

export const QUALITY_SETTINGS = {
  DRAFT: { layer: 0.28, timeCoeff: 0.85, priceCoeff: 0.95, baseHours: 0.25 },
  STANDARD: { layer: 0.2, timeCoeff: 1.0, priceCoeff: 1.0, baseHours: 0.35 },
  FINE: { layer: 0.12, timeCoeff: 1.4, priceCoeff: 1.15, baseHours: 0.5 },
} as const;
