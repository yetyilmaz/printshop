import { NextResponse } from "next/server";
import fs from "fs/promises";
import path from "path";
import crypto from "crypto";

export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    const formData = await req.formData();
    const files = formData.getAll("files") as File[];

    if (files.length === 0) {
      return NextResponse.json(
        { error: "No files provided" },
        { status: 400 }
      );
    }

    const uploadsDir = path.join(process.cwd(), "uploads");
    
    // Ensure uploads directory exists
    try {
      await fs.mkdir(uploadsDir, { recursive: true });
    } catch (e) {
      // Directory already exists
    }

    const fileIds: string[] = [];

    for (const file of files) {
      // Generate unique filename
      const fileId = crypto.randomUUID();
      const ext = path.extname(file.name);
      const filename = `${fileId}${ext}`;
      const filepath = path.join(uploadsDir, filename);

      // Read and write file
      const buffer = await file.arrayBuffer();
      await fs.writeFile(filepath, Buffer.from(buffer));

      fileIds.push(filename);
    }

    return NextResponse.json({ fileIds });
  } catch (e: any) {
    console.error("File upload error:", e);
    return NextResponse.json(
      { error: e?.message ?? "Upload failed" },
      { status: 500 }
    );
  }
}
