"use client";

import { motion } from "framer-motion";

const features = [
    { t: "Расчёт цены", d: "Мок-алгоритм по материалу, весу и времени. Позже заменишь на анализ STL/слайсер." },
    { t: "Плавные анимации", d: "Минималистичная сетка, мягкие тени, glass UI и плавные переходы страниц." },
    { t: "Поток оплаты", d: "После заказа выдаём страницу оплаты. В демо — кнопки “пометить как оплачено”." },
];

export function FeatureSection() {
    return (
        <motion.section
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5 }}
            className="mt-[14px] grid gap-[14px] md:grid-cols-3"
        >
            {features.map((f, i) => (
                <motion.div
                    key={f.t}
                    initial={{ opacity: 0, y: 15 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: i * 0.1, duration: 0.5 }}
                    className="glass p-[18px] rounded-[26px]"
                >
                    <div className="font-semibold text-[#0a0a0a]">{f.t}</div>
                    <div className="mt-[6px] text-[13px] text-[rgba(10,10,10,0.62)]">
                        {f.d}
                    </div>
                </motion.div>
            ))}
        </motion.section>
    );
}
