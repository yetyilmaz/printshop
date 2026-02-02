import { NextResponse } from "next/server";
import fs from "fs/promises";
import path from "path";

export async function GET() {
  try {
    const pricesPath = path.join(process.cwd(), "prices.json");
    const pricesData = await fs.readFile(pricesPath, "utf-8");
    const prices = JSON.parse(pricesData);

    if (prices.materials) {
      const materials = Object.keys(prices.materials);
      return NextResponse.json({ materials });
    }

    return NextResponse.json({ materials: ["PETG", "ASA", "PA", "COPA"] });
  } catch (e) {
    console.log("Error reading materials:", e);
    return NextResponse.json({ materials: ["PETG", "ASA", "PA", "COPA"] });
  }
}
