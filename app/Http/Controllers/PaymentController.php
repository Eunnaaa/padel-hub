<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::with(['booking.court.venue', 'booking'])
                ->whereHas('booking', fn ($q) => $q->where('user_id', Auth::id()))
                ->latest()
                ->get(),
        ]);
    }

    public function invoice(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        return Inertia::render('Payments/Invoice', [
            'booking' => $booking->load('court.venue', 'payment'),
        ]);
    }

    public function create(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_if($booking->payment_status === 'paid', 422, 'Already paid.');

        return Inertia::render('Payments/Create', [
            'booking' => $booking->load('court.venue'),
            'snapToken' => null,
        ]);
    }

    public function store(PaymentRequest $request, Booking $booking): Response|RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_if($booking->payment_status === 'paid', 422, 'Already paid.');

        $enabled = $this->midtransEnabled();

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'method' => $request->validated('method'),
            'provider' => $enabled ? 'midtrans' : 'manual',
            'status' => $enabled ? 'pending' : 'paid',
            'transaction_reference' => $enabled
                ? 'MID-'.Str::upper(Str::random(10))
                : 'MAN-'.Str::upper(Str::random(10)),
        ]);

        if (! $enabled) {
            $this->markPaid($booking, $payment);

            return to_route('bookings.index')->with('success', 'Payment recorded.');
        }

        $snapToken = $this->createSnapToken($booking, $payment);

        return Inertia::render('Payments/Create', [
            'booking' => $booking->load('court.venue'),
            'snapToken' => $snapToken,
            'midtransClientKey' => config('midtrans.client_key'),
        ]);
    }

    public function notification(Request $request): JsonResponse
    {
        if (! $this->midtransEnabled()) {
            return response()->json(['error' => 'midtrans disabled'], 503);
        }

        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload) || ! isset($payload['signature_key'], $payload['order_id'])) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        $signature = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key'),
        );

        if (! hash_equals($signature, $payload['signature_key'])) {
            return response()->json(['error' => 'invalid signature'], 403);
        }

        $payment = Payment::where('transaction_reference', $payload['order_id'])->first();
        if (! $payment) {
            return response()->json(['error' => 'payment not found'], 404);
        }

        $status = match ($payload['transaction_status'] ?? 'pending') {
            'capture', 'settlement' => 'paid',
            'cancel', 'deny', 'expire' => 'failed',
            'refund', 'refund_chargeback' => 'refunded',
            default => 'pending',
        };

        $payment->update(['status' => $status]);
        if ($status === 'paid') {
            $this->markPaid($payment->booking, $payment);
        }

        return response()->json(['ok' => true]);
    }

    private function createSnapToken(Booking $booking, Payment $payment): string
    {
        $this->configureMidtrans();

        $params = [
            'transaction_details' => [
                'order_id' => $payment->transaction_reference,
                'gross_amount' => (int) round((float) $booking->total_amount),
            ],
            'customer_details' => [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
            ],
            'item_details' => [[
                'id' => (string) $booking->court_id,
                'price' => (int) round((float) $booking->total_amount),
                'quantity' => 1,
                'name' => "Padel {$booking->court->name} — {$booking->court->venue->name}",
            ]],
        ];

        return Snap::getSnapToken($params);
    }

    private function configureMidtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    private function midtransEnabled(): bool
    {
        return (bool) config('midtrans.server_key');
    }

    private function markPaid(Booking $booking, Payment $payment): void
    {
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'locked_until' => null,
        ]);

        $booking->user->notify(new BookingNotification(
            'Pembayaran dikonfirmasi',
            "Pembayaran booking {$booking->court->name} sebesar Rp ".number_format((float) $booking->total_amount, 0, ',', '.').' diterima.',
            route('payments.invoice', $booking),
        ));
    }
}
