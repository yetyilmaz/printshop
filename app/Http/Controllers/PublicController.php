<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortfolioService;
use App\Services\MaterialSpecsService;
use App\Repositories\PortfolioRepository;

class PublicController extends Controller
{
    public function __construct(
        protected PortfolioService $portfolioService,
        protected MaterialSpecsService $materialSpecsService,
        protected PortfolioRepository $portfolioRepository
    ) {}

    public function home()
    {
        // Get podium category projects for homepage showcase
        $podiumCategory = $this->portfolioRepository->getPodiumCategory();
        $podiumProjects = collect([]);
        
        if ($podiumCategory) {
            $items = $this->portfolioRepository->getFeaturedItems($podiumCategory->id);
            $podiumProjects = $this->portfolioService->transformItems($items);
        }
        
        return view('welcome', compact('podiumProjects'));
    }

    public function portfolio()
    {
        $categories = $this->portfolioRepository->getCategoriesWithCount();
        $items = $this->portfolioRepository->getAllWithRelations();

        // Transform items for JavaScript
        $projectsData = $this->portfolioService->transformItems($items);

        return view('portfolio.index', compact('items', 'categories', 'projectsData'));
    }

    public function portfolioItem($slug)
    {
        $item = \App\Models\PortfolioItem::with('images')->where('slug', $slug)->firstOrFail();
        return view('portfolio.show', compact('item'));
    }

    public function materialsGuide()
    {
        $materials = \Illuminate\Support\Facades\Cache::remember('materials_guide', 86400, function () {
            return \App\Models\Material::orderBy('type')->orderBy('name')->get();
        });
        
        return view('materials.guide', compact('materials'));
    }
}
