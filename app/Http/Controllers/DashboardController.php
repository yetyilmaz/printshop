<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            $visitsToday = Visit::where('visit_date', now()->toDateString())->count();
            
            // Fetch relevant orders and group by status to avoid repeated filtering in the view
            $orders = Order::with(['user', 'items'])
                ->whereIn('status', ['needs_review', 'quoted', 'printing', 'paid', 'done', 'delivered'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('status');
                
            return view('dashboard', compact('visitsToday', 'orders'));
        }

        // Regular user view
        $orders = $user->orders()->orderBy('created_at', 'desc')->get();
        return view('user_dashboard', compact('orders'));
    }
}
