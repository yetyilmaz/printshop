import { NextResponse } from "next/server";
import fs from "fs/promises";
import path from "path";
import crypto from "crypto";

export const runtime = "nodejs";
const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
const UPLOADS_DIR = path.join(process.cwd(), "public/uploads");

async function ensureUploadsDir() {
  try {
    await fs.access(UPLOADS_DIR);
  } catch {
    await fs.mkdir(UPLOADS_DIR, { recursive: true });
  }
}

export async function POST(req: Request) {
  try {
    const formData = await req.formData();
    const file = formData.get("file") as File;

    if (!file) {
      return NextResponse.json({ error: "No file provided" }, { status: 400 });
    }

    // Проверяем расширение
    const filename = file.name.toLowerCase();
    if (!filename.endsWith(".glb")) {
      return NextResponse.json({ error: "Only .glb files are allowed" }, { status: 400 });
    }

    // Проверяем размер
    if (file.size > MAX_FILE_SIZE) {
      return NextResponse.json(
        { error: `File too large. Max ${MAX_FILE_SIZE / 1024 / 1024}MB` },
        { status: 400 }
      );
    }

    await ensureUploadsDir();

    const fileId = crypto.randomUUID();
    const ext = ".glb";
    const storedFilename = `${fileId}${ext}`;
    const storedPath = path.join(UPLOADS_DIR, storedFilename);
    const publicPath = `/uploads/${storedFilename}`;

    const buffer = await file.arrayBuffer();
    await fs.writeFile(storedPath, new Uint8Array(buffer));

    return NextResponse.json({
      fileId,
      originalName: file.name,
      sizeBytes: file.size,
      storedPath: publicPath,
      ext,
      success: true,
    });
  } catch (e: any) {
    console.error("GLB upload error:", e);
    return NextResponse.json({ error: e?.message ?? "Upload error" }, { status: 500 });
  }
}
