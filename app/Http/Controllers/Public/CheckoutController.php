<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CheckoutRequest;
use App\Models\Enrolment;
use App\Models\Payment;
use App\Services\PaymentGateway;
use App\Support\Basket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly PaymentGateway $gateway)
    {
    }

    public function show(): View|RedirectResponse
    {
        $courses = $this->payableCourses();

        if ($courses->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty. Add a course before checking out.']);
        }

        return view('landing.checkout', [
            'courses' => $courses,
            'total' => $courses->sum('price'),
        ]);
    }

    public function pay(CheckoutRequest $request): RedirectResponse
    {
        $courses = $this->payableCourses();

        if ($courses->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $total = (float) $courses->sum('price');
        $method = $total > 0 ? $request->input('method') : 'free';

        $result = $this->gateway->charge($method, $total, [
            'number' => $request->input('card_number'),
            'name' => $request->input('card_name'),
            'expiry' => $request->input('card_expiry'),
            'cvc' => $request->input('card_cvc'),
        ]);

        if (! $result['success']) {
            return back()->withInput($request->except(['card_number', 'card_cvc']))
                ->withErrors(['payment' => $result['message']]);
        }

        $payments = DB::transaction(function () use ($courses, $method, $result) {
            $payments = collect();

            foreach ($courses as $index => $course) {
                $enrolment = Enrolment::query()->create([
                    'course_id' => $course->id,
                    'student_id' => auth()->id(),
                    'status' => 'active',
                    'amount_paid' => $course->price,
                    'progress' => 0,
                    'enrolled_at' => now(),
                ]);

                $payments->push(Payment::query()->create([
                    'enrolment_id' => $enrolment->id,
                    // One gateway transaction, one receipt reference per course line.
                    'reference' => $result['reference'].($courses->count() > 1 ? '-'.($index + 1) : ''),
                    'amount' => $course->price,
                    'method' => $method,
                    'status' => 'paid',
                    'card_brand' => $result['brand'],
                    'card_last4' => $result['last4'],
                    'paid_at' => now(),
                ]));

                $course->increment('enrolments_count');
            }

            return $payments;
        });

        Basket::clear(Basket::CART);

        return redirect()
            ->route('checkout.receipt', $payments->first()->reference)
            ->with('success', 'Payment successful! You now have access to your course materials.');
    }

    public function receipt(string $reference): View
    {
        $base = preg_replace('/-\d+$/', '', $reference);

        $payments = Payment::query()
            ->with('enrolment.course.instructor')
            ->where(fn ($q) => $q->where('reference', $base)->orWhere('reference', 'like', $base.'-%'))
            ->whereHas('enrolment', fn ($q) => $q->where('student_id', auth()->id()))
            ->orderBy('id')
            ->get();

        abort_if($payments->isEmpty(), 404);

        return view('landing.receipt', [
            'payments' => $payments,
            'reference' => $base,
            'total' => $payments->sum('amount'),
        ]);
    }

    /** Cart courses the logged-in student has not already purchased. */
    private function payableCourses()
    {
        $owned = Enrolment::query()->where('student_id', auth()->id())->pluck('course_id')->all();

        $courses = Basket::courses(Basket::CART)->reject(fn ($course) => in_array($course->id, $owned, true));

        session([Basket::CART => $courses->pluck('id')->all()]);

        return $courses->values();
    }
}
