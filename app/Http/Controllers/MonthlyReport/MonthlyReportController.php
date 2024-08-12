<?php

namespace App\Http\Controllers\MonthlyReport;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderState;
use App\Models\PersonalAccessToken;
use App\Models\Service;
use App\Models\Sponsor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyReportController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $report = array();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        // Client
        // 1- total number of clients
        $report['total_clients'] = Client::count();

        // 2- number of clients this month
        $report['clients_this_month'] = Client::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // 3- number of active clients
        $activeClientsCount = PersonalAccessToken::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('tokenable_type', User::class)
            ->whereHas('tokenable', function ($query) {
                $query->where('userable_type', Client::class);
            })
            ->count();
        $report['active_clients'] = $activeClientsCount;

        // 4- top client by revenue
        $topClient = Client::select(DB::raw("CONCAT(first_name, ' ', last_name) AS name"), 'image', DB::raw('SUM(CASE WHEN transactions.transaction_status_id = 1 AND transactions.transaction_type_id = 1 THEN transactions.balance ELSE 0 END) - SUM(CASE WHEN transactions.transaction_status_id = 2 AND transactions.transaction_type_id = 1 THEN transactions.balance ELSE 0 END) as total_payments'))
            ->join('transactions', 'clients.id', '=', 'transactions.user_id')
            ->groupBy('clients.id')
            ->orderBy('total_payments', 'DESC')
            ->first();
        $report['top_client_by_revenue'] = $topClient;

        // 5- top client by number of events
        $topClient = Client::select(DB::raw("CONCAT(first_name, ' ', last_name) AS name"), 'image', DB::raw('COUNT(orders.id) as orders_count'))
            ->join('orders', 'clients.id', '=', 'orders.client_id')
            ->groupBy('clients.id')
            ->orderBy('orders_count', 'DESC')
            ->first();
        $report['top_client_by_events'] = $topClient;

        // Sponsor
        // 6- total number of sponsors
        $report['total_sponsors'] = Sponsor::count();

        // 7- number of sponsors this month
        $report['sponsors_this_month'] = Sponsor::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // 8- number of active sponsors
        $activeSponsorsCount = PersonalAccessToken::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('tokenable_type', User::class)
            ->whereHas('tokenable', function ($query) {
                $query->where('userable_type', Sponsor::class);
            })
            ->count();
        $report['active_sponsors'] = $activeSponsorsCount;

        // 9- top sponsor by revenue
        $topSponsor = Sponsor::select('sponsors.*', DB::raw('SUM(bookings.price) as total_revenue'))
            ->join('services', 'sponsors.service_id', '=', 'services.id')
            ->join('bookings', 'services.id', '=', 'bookings.service_id')
            ->groupBy('sponsors.id')
            ->orderBy('total_revenue', 'DESC')
            ->first();
        $report['top_sponsor_by_revenue'] = $topSponsor;

        // 10- top sponsor by deliverd
        $topSponsor = Sponsor::select('sponsors.*', DB::raw('COUNT(bookings.id) as bookings_count'))
            ->join('services', 'sponsors.service_id', '=', 'services.id')
            ->join('bookings', 'services.id', '=', 'bookings.service_id')
            ->groupBy('sponsors.id')
            ->orderBy('bookings_count', 'DESC')
            ->first();
        $report['top_sponsor_by_deliverd'] = $topSponsor;

        // Events
        // 11- total number of events
        $report['total_events'] = Order::count();

        // 12- number of events for each week in this month
        $weeks = [];
        $currentWeekStart = $startOfMonth->copy();
        $cnt = 1;

        while ($currentWeekStart->lte($endOfMonth)) {
            // Ensure the start of the week is within this month
            $currentWeekStart = $currentWeekStart->startOfWeek();

            // Calculate the end of the week, ensuring it doesn't exceed the end of the month
            $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();

            // If the end of the week goes beyond the end of the month, limit it to the end of the month
            if ($currentWeekEnd->gt($endOfMonth)) {
                $currentWeekEnd = $endOfMonth->copy();
            }

            // Store the week and the number of events in that week
            $weeks[] = [
                'week' => $cnt++,
                'num_of_events' => Order::whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])->count()
            ];

            if ($cnt == 4) {
                dd($currentWeekStart . ' ' . $currentWeekEnd);
            }

            // Move to the start of the next week
            $currentWeekStart = $currentWeekEnd->copy()->addDay();
        }
        $report['events_in_each_week'] = $weeks;

        // 13- number of events by status
        $pendingEventsCount = Order::where('order_state_id', OrderState::ORDERSTATE_PENDING)->count();
        $in_PreparationEventsCount = Order::where('order_state_id', OrderState::ORDERSTATE_IN_PREPARATION)->count();
        $doneEventsCount = Order::where('order_state_id', OrderState::ORDERSTATE_DONE)->count();
        $canceledEventsCount = Order::where('order_state_id', OrderState::ORDERSTATE_CANCELED)->count();
        $report['total_events_by_status'] = [
            'Pending' => $pendingEventsCount,
            'In_Preparation' => $in_PreparationEventsCount,
            'Done' => $doneEventsCount,
            'Canceled' => $canceledEventsCount
        ];

        // 14- number of orders for each category
        $categoryOrders = DB::table('bookings')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->join('categouries', 'services.categoury_id', '=', 'categouries.id')
            ->select('categouries.name', DB::raw('COUNT(bookings.id) as total_orders'))
            ->groupBy('categouries.name')
            ->orderBy('total_orders', 'desc')
            ->get();
        $report['number of orders for each category'] = $categoryOrders;

        // Payments
        // 15- total payments
        $report['total_payments'] = DB::table('bookings')->sum('price');

        // 16- Average payment per event
        $averagePaymentPerOrder = DB::table('bookings')
            ->select(DB::raw('AVG(order_payment) as avg_payments_per_order'))
            ->from(DB::raw('(SELECT order_id, SUM(price) as order_payment FROM bookings GROUP BY order_id) as subquery'))
            ->value('avg_payments_per_order');
        $report['avg_payments_per_order'] = $averagePaymentPerOrder == null ? 0 : $averagePaymentPerOrder;

        // Services
        // 17- Most requested service
        $mostRequestedService = Service::select('services.*', DB::raw('COUNT(services.id) as service_count'))
            ->join('bookings', 'services.id', '=', 'bookings.service_id')
            ->groupBy('services.id')
            ->orderBy('service_count', 'DESC')
            ->latest(5);
        $report['most_requested_service'] = $mostRequestedService;

        // 18- Average number of services per order
        $averageServicesPerOrder = DB::table('bookings')
            ->select(DB::raw('AVG(service_count) as avg_services_per_order'))
            ->from(DB::raw('(SELECT order_id, COUNT(service_id) as service_count FROM bookings GROUP BY order_id) as subquery'))
            ->value('avg_services_per_order');
        $report['avg_services_per_order'] = $averageServicesPerOrder == null ? 0 : $averageServicesPerOrder;

        return $this->sendResponse($report);
    }
}
