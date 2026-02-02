"use client";

import { motion } from "framer-motion";
import Link from "next/link";

export function HeroSection() {
    return (
        <motion.section
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="p-[34px] rounded-[36px] bg-white/65 border border-[rgba(10,10,10,0.1)] backdrop-blur-[14px] shadow-[0_20px_60px_rgba(10,10,10,0.08)] relative overflow-hidden"
        >
            {/* Background Gradients inside Hero */}
            <div className="absolute inset-[-2px] bg-[radial-gradient(600px_180px_at_50%_0%,rgba(0,0,0,0.07),transparent_60%)] opacity-55 pointer-events-none" />

            <div className="relative z-10">
                <div className="pill mb-[10px] w-fit">
                    3D печать на заказ • загрузка STL • мок-расчёт • QR-оплата
                </div>

                <h1 className="text-[44px] leading-[1.05] tracking-[-0.04em] font-bold text-[#0a0a0a] my-[10px]">
                    Минимализм, который продаёт.
                </h1>

                <p className="text-[rgba(10,10,10,0.62)] max-w-[56ch] m-0">
                    Загрузи STL, выбери материал и качество — получи расчёт и ссылку на оплату.
                    Демо показывает UX-поток и интерфейс. Дальше подключим реальные расчёты, оплату и админку.
                </p>

                <div className="mt-8 flex flex-wrap gap-[12px]">
                    <Link
                        href="/portfolio"
                        className="btn text-[15px] px-6 py-3 h-auto"
                    >
                        Примеры работ
                    </Link>
                </div>
            </div>
        </motion.section>
    );
}
