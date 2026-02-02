import { NextResponse } from "next/server";
import { readPriceConfig } from "@/lib/priceConfig";

type CalcRequest = {
  // в MVP volume не считаем точно, используем bbox-оценку
  bbox: { x: number; y: number; z: number };
  volumeMm3?: number;
  material: string;
  quality: "DRAFT" | "STANDARD" | "FINE";
  infill: 15 | 25 | 40 | 60 | 100;
  qty: number;
  supports: boolean;
  rush: boolean;
};

function clampQty(qty: number) {
  if (!Number.isFinite(qty)) return 1;
  return Math.max(1, Math.min(999, Math.floor(qty)));
}

// MVP: Estimate volume from BBox
function estimateVolumeMm3FromBBox(bbox: { x: number; y: number; z: number }) {
  const boxVol = Math.max(0, bbox.x) * Math.max(0, bbox.y) * Math.max(0, bbox.z);
  const kShape = 0.35; // стартовая калибровка
  return boxVol * kShape;
}

function infillCoeff(infill: number) {
  if (infill <= 15) return 0.55;
  if (infill <= 25) return 0.65;
  if (infill <= 40) return 0.78;
  if (infill <= 60) return 0.92;
  return 1.1; // 100%
}

export async function POST(req: Request) {
  try {
    const body = (await req.json()) as CalcRequest;
    const config = await readPriceConfig();

    const qty = clampQty(body.qty);
    const mat = config.materials[body.material];
    const q = config.quality[body.quality];

    if (!mat) {
      // Fallback to defaults if material not in DB/Config, or error
      // Ideally we should fail if material is invalid.
      // Retaining fail behavior for safety.
      return NextResponse.json(
        { error: `Material ${body.material} not found` },
        { status: 400 }
      );
    }

    // Safety check for quality
    if (!q) {
      return NextResponse.json({ error: "Invalid quality" }, { status: 400 });
    }

    // Precise volume if available, else estimate
    const volumeMm3 = body.volumeMm3 && body.volumeMm3 > 0
      ? body.volumeMm3
      : estimateVolumeMm3FromBBox(body.bbox);

    const volumeCm3 = volumeMm3 / 1000;

    const grams = volumeCm3 * mat.density * infillCoeff(body.infill) * 1.12;
    const gramsOne = Math.max(1, grams);

    // Base Calculation
    // Using config.quality[].timeMultiplier / baseHours logic?
    // Wait, config.quality structure in priceConfig.ts has timeMultiplier but maybe not baseHours?
    // Let's check priceConfig.ts structure again.
    // It has timeMultiplier and priceMultiplier.
    // It does NOT have baseHours/layer/timeCoeff like the old route.
    // We should adapt the formula to use the new config structure or hardcode base/layer if missing.
    // The old route used `baseHours`. New config doesn't seem to have it.
    // I'll assume baseHours of 0.35 (standard) scaled by timeMultiplier.
    const baseHours = 0.35;

    const hoursOne = (baseHours + volumeCm3 * 0.03 * q.timeMultiplier) * (body.supports ? 1.1 : 1.0);
    const hours = Math.max(0.2, hoursOne);

    const materialCost = gramsOne * mat.pricePerGram;
    const machineCost = hours * mat.pricePerHour; // Note: pricePerHour in config
    const supportsFee = body.supports ? config.other.supportsBaseFee : 0;
    const setupFee = mat.setupFee;

    let subtotal = (materialCost + machineCost + supportsFee + setupFee) * q.priceMultiplier;

    if (body.rush) subtotal = subtotal * config.other.rushMultiplier;

    // Dynamic Discount
    const { qty: discQty, sum: discSum } = config.other.discountThreshold;
    const eligibleQty = qty >= discQty;
    // We could also check sum threshold, but let's stick to qty as per user request
    const discount = eligibleQty ? config.other.discountPercent : 0;

    const totalOne = subtotal * (1 - discount);

    const total = Math.max(config.other.minOrderPrice, totalOne * qty);

    return NextResponse.json({
      volumeMm3: Math.round(volumeMm3),
      gramsOne: Math.round(gramsOne),
      hoursOne: Math.round(hours * 100) / 100, // Returning unit hours, frontend multiplies
      discount,
      breakdown: {
        materialCost: Math.round(materialCost),
        machineCost: Math.round(machineCost),
        setupFee: Math.round(setupFee),
        supportsFee: Math.round(supportsFee),
        subtotal: Math.round(subtotal),
      },
      total: Math.round(total),
      note: "MVP-оценка по габаритам.",
    });
  } catch (e: any) {
    console.error("Calc error:", e);
    return NextResponse.json({ error: e?.message ?? "Calc error" }, { status: 500 });
  }
}