<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Area;
use App\Models\BoardingAnnouncement;
use App\Models\BoardingHouse;
use App\Models\BoardingQuestion;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\ListingReport;
use App\Models\MaintenanceTicket;
use App\Models\MitraExpense;
use App\Models\MitraProfile;
use App\Models\SurveyBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $data = ['user' => $user];

        if ($user->isAdmin()) {
            $data['pendingMitra'] = MitraProfile::with('user')
                ->where('verification_status', MitraProfile::STATUS_PENDING)
                ->latest()
                ->get();
            $data['pendingKos'] = BoardingHouse::with(['area', 'mitra', 'roomTypes'])
                ->where('status', BoardingHouse::STATUS_PENDING_REVIEW)
                ->latest()
                ->get();
            $data['recentBookings'] = Booking::with(['user', 'roomType.boardingHouse.mitra'])
                ->latest()
                ->take(6)
                ->get();
            $data['recentLogs'] = ActivityLog::with('user')->orderByDesc('id')->take(6)->get();
            $data['openReportsCount'] = ListingReport::where('status', ListingReport::STATUS_OPEN)->count();

            $areasWithStats = Area::with(['boardingHouses' => fn ($q) => $q->published()->with('roomTypes')])
                ->orderBy('name')
                ->get()
                ->map(function (Area $area) {
                    $total = $area->boardingHouses->sum(fn ($k) => $k->roomTypes->sum('total_rooms'));
                    $avail = $area->boardingHouses->sum(fn ($k) => $k->roomTypes->sum('available_rooms'));

                    return [
                        'area' => $area->name,
                        'kos' => $area->boardingHouses->count(),
                        'occupied' => max(0, $total - $avail),
                        'available' => $avail,
                    ];
                })
                ->values();

            $months = collect(range(5, 0))->map(function (int $offset) {
                $date = now()->startOfMonth()->subMonths($offset);
                $bookings = Booking::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED]);

                return [
                    'label' => $date->translatedFormat('M Y'),
                    'count' => (clone $bookings)->count(),
                    'revenue' => (int) $bookings->sum('total_price'),
                ];
            })->values();

            $data['areaChart'] = $areasWithStats;
            $data['monthlyChart'] = $months;

            $data['counts'] = [
                'published_kos' => BoardingHouse::published()->count(),
                'pending_kos' => $data['pendingKos']->count(),
                'approved_mitra' => MitraProfile::where('verification_status', MitraProfile::STATUS_APPROVED)->count(),
                'pending_mitra' => $data['pendingMitra']->count(),
                'total_users' => User::count(),
                'total_bookings' => Booking::count(),
                'confirmed_revenue' => Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])->sum('total_price'),
                'total_areas' => Area::count(),
                'total_facilities' => Facility::count(),
                'open_reports' => $data['openReportsCount'],
            ];
        } elseif ($user->isMitra()) {
            $user->load('mitraProfile');

            $myKos = BoardingHouse::with(['area', 'roomTypes.facilities', 'images'])
                ->where('mitra_id', $user->id)
                ->latest()
                ->get();

            $mitraBookingsQuery = Booking::whereHas(
                'roomType.boardingHouse',
                fn ($q) => $q->where('mitra_id', $user->id)
            );

            $pendingBookings = (clone $mitraBookingsQuery)
                ->with(['user', 'roomType.boardingHouse', 'payment'])
                ->where('status', Booking::STATUS_WAITING_VERIFICATION)
                ->latest()
                ->get();

            $activeTenants = (clone $mitraBookingsQuery)
                ->with(['user', 'roomType.boardingHouse'])
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                ->latest()
                ->take(6)
                ->get();

            $totalRooms = $myKos->sum(fn ($k) => $k->roomTypes->sum('total_rooms'));
            $availableRooms = $myKos->sum(fn ($k) => $k->roomTypes->sum('available_rooms'));
            $confirmedIncome = (int) (clone $mitraBookingsQuery)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                ->sum('total_price');
            $totalExpenses = (int) MitraExpense::where('mitra_id', $user->id)->sum('amount');

            $mitraMonthlyChart = collect(range(5, 0))->map(function (int $offset) use ($user) {
                $date = now()->startOfMonth()->subMonths($offset);
                $income = Booking::whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
                    ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_price');
                $expense = MitraExpense::where('mitra_id', $user->id)
                    ->whereYear('expense_date', $date->year)
                    ->whereMonth('expense_date', $date->month)
                    ->sum('amount');

                return [
                    'label' => $date->translatedFormat('M Y'),
                    'income' => (int) $income,
                    'expense' => (int) $expense,
                ];
            })->values();

            $data['myKos'] = $myKos;
            $data['pendingBookings'] = $pendingBookings;
            $data['activeTenants'] = $activeTenants;
            $data['kosWithoutRooms'] = $myKos->filter(fn ($k) => $k->roomTypes->isEmpty());
            $data['mitraMonthlyChart'] = $mitraMonthlyChart;
            $data['mitraStats'] = [
                'total_kos' => $myKos->count(),
                'published_kos' => $myKos->where('status', BoardingHouse::STATUS_PUBLISHED)->count(),
                'total_rooms' => $totalRooms,
                'available_rooms' => $availableRooms,
                'occupied_rooms' => max(0, $totalRooms - $availableRooms),
                'waiting_verification' => $pendingBookings->count(),
                'confirmed_income' => $confirmedIncome,
                'total_expenses' => $totalExpenses,
                'net_income' => $confirmedIncome - $totalExpenses,
                'pending_surveys' => SurveyBooking::whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
                    ->where('status', SurveyBooking::STATUS_PENDING)->count(),
                'open_tickets' => MaintenanceTicket::whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
                    ->whereIn('status', [MaintenanceTicket::STATUS_OPEN, MaintenanceTicket::STATUS_IN_PROGRESS])->count(),
                'unanswered_questions' => BoardingQuestion::whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $user->id))
                    ->whereNull('answer')->count(),
            ];
        } else {
            $myBookings = Booking::with(['roomType.boardingHouse.area', 'payment', 'review'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $savedKos = $user->savedBoardingHouses()
                ->published()
                ->with(['area', 'images', 'roomTypes', 'facilities'])
                ->withMin('roomTypes as min_monthly_price', 'price_monthly')
                ->withSum('roomTypes as total_available_rooms', 'available_rooms')
                ->take(4)
                ->get();

            $activeKosIds = $myBookings
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                ->map(fn ($b) => $b->roomType?->boarding_house_id)
                ->filter()
                ->unique()
                ->values();

            $announcements = $activeKosIds->isNotEmpty()
                ? BoardingAnnouncement::with('boardingHouse')
                    ->whereIn('boarding_house_id', $activeKosIds)
                    ->orderByDesc('is_pinned')
                    ->latest()
                    ->take(5)
                    ->get()
                : collect();

            $upcomingSurveys = SurveyBooking::with(['boardingHouse', 'roomType'])
                ->where('user_id', $user->id)
                ->whereIn('status', [SurveyBooking::STATUS_PENDING, SurveyBooking::STATUS_APPROVED, SurveyBooking::STATUS_RESCHEDULED])
                ->orderBy('preferred_date')
                ->take(4)
                ->get();

            $data['myBookings'] = $myBookings;
            $data['activeBooking'] = $myBookings->first(
                fn ($b) => in_array($b->status, [
                    Booking::STATUS_PENDING_PAYMENT,
                    Booking::STATUS_WAITING_VERIFICATION,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                ], true)
            );
            $data['savedKos'] = $savedKos;
            $data['announcements'] = $announcements;
            $data['upcomingSurveys'] = $upcomingSurveys;
            $data['penyewaStats'] = [
                'total_bookings' => $myBookings->count(),
                'active_bookings' => $myBookings->whereIn('status', [
                    Booking::STATUS_PENDING_PAYMENT,
                    Booking::STATUS_WAITING_VERIFICATION,
                    Booking::STATUS_CONFIRMED,
                ])->count(),
                'saved_count' => $user->savedBoardingHouses()->published()->count(),
                'survey_count' => $upcomingSurveys->count(),
                'open_tickets' => MaintenanceTicket::where('user_id', $user->id)
                    ->whereIn('status', [MaintenanceTicket::STATUS_OPEN, MaintenanceTicket::STATUS_IN_PROGRESS])
                    ->count(),
            ];
        }

        return view('dashboard', $data);
    }
}
