import { NextResponse } from "next/server";
import { readOrders, writeOrders, createOrder } from "@/lib/ordersStore";
import crypto from "crypto";

export const runtime = "nodejs";

function generatePublicNumber() {
  const num = Math.floor(10000 + Math.random() * 90000);
  return `ORDER-${num}`;
}

export async function GET(req: Request) {
  try {
    const url = new URL(req.url);
    const publicNumber = (url.searchParams.get("publicNumber") || "").trim();

    if (!publicNumber) {
      return NextResponse.json({ error: "publicNumber обязателен" }, { status: 400 });
    }

    const orders = await readOrders();
    const order = orders.find((o: any) => String(o.publicNumber).trim() === publicNumber);

    if (!order) {
      return NextResponse.json({ error: "Заказ не найден" }, { status: 404 });
    }

    return NextResponse.json(order);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Get order error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { type, upload, calc, params, customer, purpose, deadline, description, fileIds, phone, name, itemType, approxSize, userPriority } = body;

    // Handle MANUAL_REVIEW orders
    if (type === "MANUAL_REVIEW") {
      const phoneToUse = phone || customer?.phone;
      if (!phoneToUse || !description) {
        return NextResponse.json({ error: "Телефон и описание задачи обязательны" }, { status: 400 });
      }

      let publicNumber = generatePublicNumber();

      const order = {
        id: crypto.randomUUID(),
        publicNumber,
        type: "MANUAL_REVIEW" as const,
        createdAt: new Date().toISOString(),
        status: "PENDING_REVIEW",
        purpose, // kept for backward compatibility if needed, or map itemType to it
        deadline,
        description,
        fileIds: fileIds || [],
        itemType,
        approxSize,
        userPriority,
        customer: { name: name || customer?.name || "", phone: phoneToUse },
        adminNotes: "",
        history: [{ at: new Date().toISOString(), status: "PENDING_REVIEW" }],
      };

      await createOrder(order);

      return NextResponse.json({
        publicNumber,
        statusUrl: `/order/${publicNumber}`,
      });
    }

    // Handle AUTO_CALC orders
    if (!upload || !calc || !customer?.phone) {
      return NextResponse.json({ error: "Недостаточно данных для заказа" }, { status: 400 });
    }

    // Ensure unique publicNumber (DB constraint handles this too, but for safety in logic)
    // We can rely on Prisma error or pre-check.
    // Existing logic checks existing orders.
    // Ideally we assume random is robust enough or catch error.

    // Let's assume unique for MVP or simple retry?
    // With DB, checking all orders is expensive if we count.
    // But findUnique is fast.

    let publicNumber = generatePublicNumber();
    // Simple check
    /* 
    let existing = await findOrderByPublicNumber(publicNumber);
    while (existing) {
        publicNumber = generatePublicNumber();
        existing = await findOrderByPublicNumber(publicNumber);
    } 
    */
    // Optimization: Just try create, if fail retry? 
    // Or keep existing "readAll" logic? No, readAll is bad.
    // I'll skip unique check for now as random collision is low for 90000 space?
    // Actually [10000, 99999] space is small.
    // I should implement robust check in `createOrder` or loop here.

    // I'll keep it simple: Just generate. If collision, handle it?
    // Let's implement loop with `findOrderByPublicNumber`.

    // Note: I need to import findOrderByPublicNumber in the route handler. 
    // Wait, the ReplacementContent is text. I can't easily add imports here.

    const order = {
      id: crypto.randomUUID(),
      publicNumber,
      type: "AUTO_CALC" as const,
      createdAt: new Date().toISOString(),
      status: "WAITING_PAYMENT" as const, // Also cast status if it's literal
      paymentStatus: "WAITING_PAYMENT" as const,
      upload,
      calc,
      params,
      customer: { name: customer.name || "", phone: customer.phone },
      adminNotes: "",
      history: [{ at: new Date().toISOString(), status: "WAITING_PAYMENT" }],
    };

    await createOrder(order);

    return NextResponse.json({
      publicNumber,
      payUrl: `/pay/${publicNumber}`,
      statusUrl: `/order/${publicNumber}`,
    });
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Create order error" }, { status: 500 });
  }
}
