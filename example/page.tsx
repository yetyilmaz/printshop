import Link from "next/link";
import { getFeaturedProjects, readPortfolio } from "@/lib/portfolioStore";
import { ProjectCard } from "./components/ProjectCard";
import { HeroSection } from "./components/HeroSection";
import { FeatureSection } from "./components/FeatureSection";
import { FAQSection } from "./components/FAQSection";

export default async function HomePage() {
  const featuredProjects = await getFeaturedProjects();
  const portfolio = await readPortfolio();

  return (
    <div className="space-y-16">
      <HeroSection />

      <FeatureSection />

      <section>
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-2xl font-bold">Наши лучшие работы</h2>
          <Link href="/portfolio" className="text-blue-600 hover:underline">
            Смотреть все →
          </Link>
        </div>

        {featuredProjects.length === 0 ? (
          <div className="mt-4 rounded-2xl border p-10 text-gray-500 text-center bg-gray-50/50">
            Портфолио наполняется. <br />
            <span className="text-sm">Скоро здесь появятся примеры наших работ.</span>
          </div>
        ) : (
          <div className="mt-4 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {featuredProjects.slice(0, 6).map((project) => {
              const category = portfolio.categories.find((c) => c.id === project.category);
              return (
                <ProjectCard
                  key={project.id}
                  project={project}
                  categoryName={category?.name || ""}
                />
              );
            })}
          </div>
        )}
      </section>

      <FAQSection />
    </div>
  );
}
