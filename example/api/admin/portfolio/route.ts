import { NextResponse } from "next/server";
import { readPortfolio, addProject, updateProject, deleteProject, addCategory, deleteCategory } from "@/lib/portfolioStore";

export const runtime = "nodejs";

function checkAdminPassword(password: string): boolean {
  return password === process.env.ADMIN_PASSWORD || password === "admin123";
}

export async function GET(req: Request) {
  try {
    const portfolio = await readPortfolio();
    return NextResponse.json(portfolio);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { password, action, data } = body;

    if (!checkAdminPassword(password || "")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    if (action === "addProject") {
      const project = await addProject(data);
      return NextResponse.json(project);
    } else if (action === "updateProject") {
      const project = await updateProject(data.id, data.updates);
      if (!project) {
        return NextResponse.json({ error: "Project not found" }, { status: 404 });
      }
      return NextResponse.json(project);
    } else if (action === "deleteProject") {
      const success = await deleteProject(data.id);
      if (!success) {
        return NextResponse.json({ error: "Project not found" }, { status: 404 });
      }
      return NextResponse.json({ success: true });
    } else if (action === "addCategory") {
      const category = await addCategory(data);
      return NextResponse.json(category);
    } else if (action === "deleteCategory") {
      const success = await deleteCategory(data.id);
      if (!success) {
        return NextResponse.json({ error: "Category not found" }, { status: 404 });
      }
      return NextResponse.json({ success: true });
    } else {
      return NextResponse.json({ error: "Unknown action" }, { status: 400 });
    }
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Error" }, { status: 500 });
  }
}
