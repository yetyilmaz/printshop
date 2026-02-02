import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import Link from "next/link";
import Header from "./components/Header";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin", "cyrillic"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin", "cyrillic"],
});

export const metadata: Metadata = {
  title: "PrintLab — 3D печать на заказ",
  description: "3D печать и прототипирование: PETG, ASA, PA/нейлон. Онлайн расчёт и заказ.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body className={`${geistSans.variable} ${geistMono.variable} antialiased`}>
        <Header />

        <main className="max-w-[1120px] mx-auto px-[18px] py-[26px] pb-[56px] min-h-[80vh]">
          {children}
        </main>

        <footer className="max-w-[1120px] mx-auto px-[18px] py-[32px] text-[12px] text-opacity-50 text-black">
          © {new Date().getFullYear()} PrintLab • демо • 3D печать • Казахстан
        </footer>
      </body>
    </html>
  );
}
