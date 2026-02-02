"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

export default function Header() {
    const pathname = usePathname();
    const isOrderPage = pathname === "/order";
    const isAdmin = pathname?.startsWith("/admin");

    return (
        <header className="glass-nav sticky top-0 z-50">
            <div className="max-w-[1120px] mx-auto px-[18px]">
                <div className="flex items-center justify-between py-[14px]">
                    <Link href="/" className="font-bold tracking-tight text-lg">
                        PrintLab<span className="opacity-45">.</span>
                    </Link>

                    {/* 
            Optional: If we want to bring back the nav links for Portfolio/Admin, 
            we can add them here. For now, matching current layout.tsx state.
          */}

                    <div className="flex gap-[10px] items-center ml-auto">
                        {!isOrderPage && !isAdmin && (
                            <Link href="/order" className="btn btn-primary shadow-lg shadow-blue-500/20 btn-glow">
                                Оформить заказ
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </header>
    );
}
