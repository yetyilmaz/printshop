import { NextResponse } from "next/server";
import path from "path";
import fs from "fs/promises";
import crypto from "crypto";
import { parseSTL } from "@/lib/stl";

export const runtime = "nodejs";

type UploadResponse = {
  fileId: string;
  originalName: string;
  sizeBytes: number;
  storedPath: string;
  ext: string;
  bbox: { x: number; y: number; z: number };
  volumeMm3: number;
  warnings: string[];
};

export async function POST(req: Request) {
  try {
    const formData = await req.formData();
    const file = formData.get("file");

    if (!file || !(file instanceof File)) {
      return NextResponse.json({ error: "Файл не найден" }, { status: 400 });
    }

    const originalName = file.name;
    const ext = path.extname(originalName).toLowerCase();

    const normalizedExt = ext.toLowerCase();
    const ALLOWED_EXTS = [".stl", ".3mf", ".jpg", ".jpeg", ".png", ".pdf"];

    if (!ALLOWED_EXTS.includes(normalizedExt)) {
      return NextResponse.json({ error: "Разрешены: .stl, .3mf, .jpg, .png, .pdf" }, { status: 400 });
    }

    const arrayBuffer = await file.arrayBuffer();
    const buffer = Buffer.from(arrayBuffer);

    const maxSizeMB = 200;
    const sizeBytes = buffer.length;
    if (sizeBytes > maxSizeMB * 1024 * 1024) {
      return NextResponse.json({ error: `Файл слишком большой. Максимум ${maxSizeMB}MB` }, { status: 400 });
    }

    const fileId = crypto.randomUUID();
    const storedName = `${fileId}${ext}`;
    const uploadsDir = path.join(process.cwd(), "public", "uploads");
    const storedPath = path.join(uploadsDir, storedName);

    await fs.mkdir(uploadsDir, { recursive: true });
    await fs.writeFile(storedPath, buffer);

    const warnings: string[] = [];

    // Calculated fields
    let bbox = { x: 0, y: 0, z: 0 };
    let volumeMm3 = 0;

    if (normalizedExt === ".stl") {
      try {
        const info = parseSTL(buffer);
        bbox = {
          x: info.bbox.max.x - info.bbox.min.x,
          y: info.bbox.max.y - info.bbox.min.y,
          z: info.bbox.max.z - info.bbox.min.z,
        };
        volumeMm3 = info.volume;

        // BBox Limit check
        const LIMIT = { x: 340, y: 320, z: 340 };
        if (bbox.x > LIMIT.x || bbox.y > LIMIT.y || bbox.z > LIMIT.z) {
          warnings.push(
            `Модель может не помещаться в область печати ${LIMIT.x}×${LIMIT.y}×${LIMIT.z} мм. Возможна печать частями (ручная оценка).`
          );
        }
      } catch (e: any) {
        console.error("STL parse error:", e);
        warnings.push("Ошибка анализа STL. Объём рассчитан приблизительно.");
        // Fallback or leave as 0
      }
    } else if (normalizedExt === ".3mf") {
      warnings.push("3MF поддержан для загрузки, но автоматический расчёт объёма пока недоступен.");
    } else {
      // Images/PDF - no calc
    }

    const res: UploadResponse = {
      fileId,
      originalName,
      sizeBytes,
      storedPath: `/uploads/${storedName}`,
      ext,
      bbox,
      volumeMm3,
      warnings,
    };

    return NextResponse.json(res);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message ?? "Upload error" }, { status: 500 });
  }
}
