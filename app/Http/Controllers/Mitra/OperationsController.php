<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\BoardingAnnouncement;
use App\Models\BoardingHouse;
use App\Models\BoardingQuestion;
use App\Models\Booking;
use App\Models\MaintenanceTicket;
use App\Models\MitraExpense;
use App\Models\Promo;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationsController extends Controller
{
    public function storeUnit(Request $request, BoardingHouse $boardingHouse, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id, 404);

        $validated = $request->validate([
            'unit_code' => ['required', 'string', 'max:30'],
            'floor_label' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:available,occupied,maintenance'],
            'tenant_name' => ['nullable', 'string', 'max:120'],
            'lease_ends_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $exists = RoomUnit::where('room_type_id', $roomType->id)
            ->where('unit_code', trim($validated['unit_code']))
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode kamar ' . $validated['unit_code'] . ' sudah terdaftar pada tipe kamar ini.');
        }

        RoomUnit::create([
            'room_type_id' => $roomType->id,
            'unit_code' => trim($validated['unit_code']),
            'floor_label' => $validated['floor_label'] ?? null,
            'status' => $validated['status'],
            'tenant_name' => $validated['tenant_name'] ?? null,
            'lease_ends_at' => $validated['lease_ends_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('status', 'Nomor kamar ' . $validated['unit_code'] . ' berhasil ditambahkan.');
    }

    public function updateUnit(Request $request, BoardingHouse $boardingHouse, RoomType $roomType, RoomUnit $unit): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id || $unit->room_type_id !== $roomType->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,maintenance'],
            'tenant_name' => ['nullable', 'string', 'max:120'],
            'lease_ends_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $unit->update([
            'status' => $validated['status'],
            'tenant_name' => $validated['status'] === RoomUnit::STATUS_AVAILABLE ? null : ($validated['tenant_name'] ?? null),
            'lease_ends_at' => $validated['status'] === RoomUnit::STATUS_AVAILABLE ? null : ($validated['lease_ends_at'] ?? null),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('status', 'Status kamar ' . $unit->unit_code . ' diperbarui.');
    }

    public function destroyUnit(BoardingHouse $boardingHouse, RoomType $roomType, RoomUnit $unit): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id || $unit->room_type_id !== $roomType->id, 404);

        $code = $unit->unit_code;
        $unit->delete();

        return back()->with('status', 'Nomor kamar ' . $code . ' dihapus.');
    }

    public function ticketsIndex(Request $request): View
    {
        $tickets = MaintenanceTicket::with(['user', 'boardingHouse', 'booking.roomType'])
            ->whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $request->user()->id))
            ->latest()
            ->paginate(15);

        return view('mitra.tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function updateTicket(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $ticket->load('boardingHouse');
        $this->authorize('update', $ticket->boardingHouse);

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
            'mitra_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'mitra_note' => $validated['mitra_note'] ?? $ticket->mitra_note,
            'resolved_at' => $validated['status'] === MaintenanceTicket::STATUS_RESOLVED ? now() : null,
        ]);

        ActivityLogger::log(
            'maintenance.' . $validated['status'],
            'Tiket perbaikan "' . $ticket->title . '" diperbarui menjadi ' . $validated['status'] . '.',
            $ticket
        );

        NotificationService::notify(
            $ticket->user_id,
            'maintenance_updated',
            'Status Laporan Perbaikan Diperbarui',
            'Laporan "' . $ticket->title . '" di ' . $ticket->boardingHouse->name . ' berstatus: ' . str_replace('_', ' ', $validated['status']) . '.',
            route('penyewa.maintenance.index')
        );

        return back()->with('status', 'Status laporan perbaikan berhasil diperbarui.');
    }

    public function promosIndex(Request $request): View
    {
        $mitraId = $request->user()->id;

        return view('mitra.promos.index', [
            'promos' => Promo::with('boardingHouse')
                ->where('mitra_id', $mitraId)
                ->latest()
                ->get(),
            'myKos' => BoardingHouse::where('mitra_id', $mitraId)->orderBy('name')->get(),
        ]);
    }

    public function storePromo(Request $request): RedirectResponse
    {
        $mitraId = $request->user()->id;

        $validated = $request->validate([
            'boarding_house_id' => ['nullable', 'exists:boarding_houses,id'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:promos,code'],
            'title' => ['required', 'string', 'max:120'],
            'discount_type' => ['required', 'in:fixed,percent'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'min_duration_months' => ['required', 'in:1,3,6,12'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
        ]);

        if (! empty($validated['boarding_house_id'])) {
            $kos = BoardingHouse::findOrFail($validated['boarding_house_id']);
            $this->authorize('update', $kos);
        }

        if ($validated['discount_type'] === Promo::TYPE_PERCENT && $validated['discount_value'] > 80) {
            return back()->withErrors(['discount_value' => 'Persentase diskon maksimal 80%.'])->withInput();
        }

        $promo = Promo::create([
            'mitra_id' => $mitraId,
            'boarding_house_id' => $validated['boarding_house_id'] ?: null,
            'code' => strtoupper(trim($validated['code'])),
            'title' => $validated['title'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_duration_months' => (int) $validated['min_duration_months'],
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'is_active' => true,
        ]);

        ActivityLogger::log('promo.created', 'Promo ' . $promo->code . ' dibuat oleh Mitra.', $promo);

        return back()->with('status', 'Kode promo ' . $promo->code . ' berhasil ditambahkan.');
    }

    public function togglePromo(Request $request, Promo $promo): RedirectResponse
    {
        abort_if($promo->mitra_id !== $request->user()->id, 403);
        $promo->update(['is_active' => ! $promo->is_active]);

        return back()->with('status', 'Status promo ' . $promo->code . ' diperbarui.');
    }

    public function destroyPromo(Request $request, Promo $promo): RedirectResponse
    {
        abort_if($promo->mitra_id !== $request->user()->id, 403);
        $promo->delete();

        return back()->with('status', 'Promo dihapus.');
    }

    public function announcementsIndex(Request $request): View
    {
        $mitraId = $request->user()->id;

        return view('mitra.announcements.index', [
            'myKos' => BoardingHouse::where('mitra_id', $mitraId)->orderBy('name')->get(),
            'announcements' => BoardingAnnouncement::with('boardingHouse')
                ->where('mitra_id', $mitraId)
                ->orderByDesc('is_pinned')
                ->latest()
                ->get(),
            'questions' => BoardingQuestion::with(['boardingHouse', 'user'])
                ->whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $mitraId))
                ->orderByRaw('CASE WHEN answer IS NULL THEN 0 ELSE 1 END')
                ->latest()
                ->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $kos = BoardingHouse::findOrFail($validated['boarding_house_id']);
        $this->authorize('update', $kos);

        $announcement = BoardingAnnouncement::create([
            'boarding_house_id' => $kos->id,
            'mitra_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        $tenantIds = Booking::whereHas('roomType', fn ($q) => $q->where('boarding_house_id', $kos->id))
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->pluck('user_id')
            ->unique();

        foreach ($tenantIds as $tenantId) {
            NotificationService::notify(
                $tenantId,
                'kos_announcement',
                'Pengumuman dari ' . $kos->name,
                $announcement->title,
                route('dashboard')
            );
        }

        return back()->with('status', 'Pengumuman berhasil dipublikasikan ke penghuni ' . $kos->name . '.');
    }

    public function destroyAnnouncement(Request $request, BoardingAnnouncement $announcement): RedirectResponse
    {
        abort_if($announcement->mitra_id !== $request->user()->id, 403);
        $announcement->delete();

        return back()->with('status', 'Pengumuman dihapus.');
    }

    public function answerQuestion(Request $request, BoardingQuestion $question): RedirectResponse
    {
        $question->load('boardingHouse');
        $this->authorize('update', $question->boardingHouse);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $question->update([
            'answer' => trim($validated['answer']),
            'answered_by' => $request->user()->id,
            'answered_at' => now(),
        ]);

        NotificationService::notify(
            $question->user_id,
            'question_answered',
            'Pertanyaan Anda di ' . $question->boardingHouse->name . ' Dijawab',
            str($question->answer)->limit(100),
            route('kos.show', $question->boardingHouse) . '#tanya-jawab'
        );

        return back()->with('status', 'Jawaban berhasil dikirim dan tampil pada halaman kos.');
    }

    public function expensesIndex(Request $request): View
    {
        $mitraId = $request->user()->id;

        $expenses = MitraExpense::with('boardingHouse')
            ->where('mitra_id', $mitraId)
            ->latest('expense_date')
            ->paginate(15);

        $grossIncome = Booking::whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $mitraId))
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->sum('total_price');

        $totalExpenses = MitraExpense::where('mitra_id', $mitraId)->sum('amount');

        return view('mitra.expenses.index', [
            'expenses' => $expenses,
            'myKos' => BoardingHouse::where('mitra_id', $mitraId)->orderBy('name')->get(),
            'grossIncome' => (int) $grossIncome,
            'totalExpenses' => (int) $totalExpenses,
            'netIncome' => (int) $grossIncome - (int) $totalExpenses,
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'boarding_house_id' => ['nullable', 'exists:boarding_houses,id'],
            'category' => ['required', 'in:wifi,air_pdam,listrik_umum,kebersihan,perbaikan,lainnya'],
            'description' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'integer', 'min:1000'],
            'expense_date' => ['required', 'date'],
        ]);

        if (! empty($validated['boarding_house_id'])) {
            $kos = BoardingHouse::findOrFail($validated['boarding_house_id']);
            $this->authorize('update', $kos);
        }

        MitraExpense::create([
            'mitra_id' => $request->user()->id,
            'boarding_house_id' => $validated['boarding_house_id'] ?: null,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
        ]);

        return back()->with('status', 'Catatan pengeluaran operasional berhasil disimpan.');
    }

    public function destroyExpense(Request $request, MitraExpense $expense): RedirectResponse
    {
        abort_if($expense->mitra_id !== $request->user()->id, 403);
        $expense->delete();

        return back()->with('status', 'Catatan pengeluaran dihapus.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $mitraId = $request->user()->id;

        $bookings = Booking::with(['user', 'roomType.boardingHouse', 'payment'])
            ->whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $mitraId))
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->latest()
            ->get();

        $filename = 'rekap-mitra-lokakos-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Booking', 'Tanggal', 'Penyewa', 'Properti Kos', 'Tipe Kamar', 'Durasi (Bulan)', 'Diskon', 'Total Tagihan', 'Status']);

            foreach ($bookings as $b) {
                fputcsv($out, [
                    $b->booking_code,
                    $b->created_at->format('Y-m-d'),
                    $b->user?->name ?? '-',
                    $b->roomType?->boardingHouse?->name ?? '-',
                    $b->roomType?->name ?? '-',
                    $b->duration_months,
                    $b->discount_amount,
                    $b->total_price,
                    $b->status,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function posterQrPdf(BoardingHouse $boardingHouse): Response
    {
        $this->authorize('update', $boardingHouse);
        $boardingHouse->load(['area', 'roomTypes', 'facilities', 'mitra.mitraProfile']);

        $publicUrl = route('kos.show', $boardingHouse);
        $qrDataUri = QrCodeService::pngDataUri($publicUrl, 8);

        $pdf = Pdf::loadView('pdf.kos-poster-qr', [
            'kos' => $boardingHouse,
            'publicUrl' => $publicUrl,
            'qrDataUri' => $qrDataUri,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Poster-QR-' . $boardingHouse->slug . '.pdf');
    }
}
