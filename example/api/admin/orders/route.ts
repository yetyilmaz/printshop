import { NextResponse } from "next/server";
import { readOrders, updateOrder, deleteOrder, findOrderByPublicNumber } from "@/lib/ordersStore";

export const runtime = "nodejs";

function checkAdminPassword(password: string): boolean {
  return password === process.env.ADMIN_PASSWORD || password === "admin123";
}

export async function GET(req: Request) {
  try {
    const url = new URL(req.url);
    const password = url.searchParams.get("password");
    const status = url.searchParams.get("status");

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    let orders = await readOrders();

    if (status) {
      orders = orders.filter((o: any) => o.status === status);
    }

    return NextResponse.json(orders);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}

export async function PATCH(req: Request) {
  try {
    const body = await req.json();
    const { password, publicNumber, updates } = body;

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const order = await findOrderByPublicNumber(publicNumber);

    if (!order) {
      return NextResponse.json({ error: "Order not found" }, { status: 404 });
    }

    // Добавляем в историю
    if (updates.status) {
      const history = order.history || [];
      history.push({
        at: new Date().toISOString(),
        status: updates.status,
      });
      updates.history = history;
    }

    await updateOrder(publicNumber, updates);
    const updated = await findOrderByPublicNumber(publicNumber); // Fetch updated

    return NextResponse.json(updated);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const body = await req.json();
    const { password, publicNumber } = body;

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const success = await deleteOrder(publicNumber);

    if (!success) {
      return NextResponse.json({ error: "Order not found" }, { status: 404 });
    }

    return NextResponse.json({ success: true, deletedPublicNumber: publicNumber });
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}
