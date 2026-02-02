import { NextResponse } from "next/server";
import { findOrderByPublicNumber } from "@/lib/ordersStore";

export const runtime = "nodejs";

export async function GET(
  _req: Request,
  { params }: { params: Promise<{ publicNumber: string }> }
) {
  try {
    const resolvedParams = await params;
    const requested = String(resolvedParams.publicNumber || "").trim();

    const order = await findOrderByPublicNumber(requested);

    if (!order) {
      return NextResponse.json({ error: "Заказ не найден" }, { status: 404 });
    }

    return NextResponse.json(order);
  } catch (e: any) {
    return NextResponse.json({ error: "Get order error" }, { status: 500 });
  }
}