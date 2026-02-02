import { NextResponse } from "next/server";
import fs from "fs/promises";
import path from "path";

export const runtime = "nodejs";

const ORDERS_PATH = path.resolve(process.cwd(), "orders.json");

async function safeReadJson() {
  try {
    const raw = await fs.readFile(ORDERS_PATH, "utf8");
    return JSON.parse(raw);
  } catch (e: any) {
    return { error: e?.message ?? String(e) };
  }
}

export async function GET() {
  const cwd = process.cwd();
  const resolved = ORDERS_PATH;

  let exists = true;
  try {
    await fs.access(resolved);
  } catch {
    exists = false;
  }

  const data = await safeReadJson();

  // чтобы ответ был лёгкий — вернём только номера
  const publicNumbers =
    Array.isArray(data) ? data.map((o: any) => o.publicNumber) : null;

  return NextResponse.json({
    cwd,
    ordersPath: resolved,
    fileExists: exists,
    publicNumbers,
    rawType: Array.isArray(data) ? "array" : typeof data,
    rawPreview: Array.isArray(data) ? data.slice(-3) : data,
  });
}