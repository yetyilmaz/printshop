import { NextResponse } from "next/server";
import { readPriceConfig, writePriceConfig } from "@/lib/priceConfig";

export const runtime = "nodejs";

// Простая проверка пароля (в боевом коде использовать JWT)
function checkAdminPassword(password: string): boolean {
  return password === process.env.ADMIN_PASSWORD || password === "admin123";
}

export async function GET(req: Request) {
  try {
    const url = new URL(req.url);
    const password = url.searchParams.get("password");

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Неверный пароль" }, { status: 401 });
    }

    const config = await readPriceConfig();
    return NextResponse.json(config);
  } catch (e: any) {
    console.error("Pricing API Error:", e);
    return NextResponse.json({ error: `Ошибка сервера: ${e?.message ?? "Unknown"}` }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { password, config } = body;

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    await writePriceConfig(config);
    return NextResponse.json({ success: true, config });
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const body = await req.json();
    const { password, action, materialKey, materialData } = body;

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const config = await readPriceConfig();

    if (action === "add" || action === "update") {
      config.materials[materialKey] = materialData;
    } else if (action === "delete") {
      delete config.materials[materialKey];
    } else {
      return NextResponse.json({ error: "Unknown action" }, { status: 400 });
    }

    await writePriceConfig(config);
    return NextResponse.json({ success: true, config });
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}
