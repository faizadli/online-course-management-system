@extends('layouts.dashboard', ['role' => 'admin'])

@section('title', 'Reports — Admin — OCMS')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Platform Reports</h1>
    <p class="text-gray-500 mt-1">Platform-wide statistics covering users, courses, enrolments and payments.</p>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Total Users', 'value' => number_format($stats['total_users']), 'sub' => $stats['new_users_this_month'].' new this month'],
        ['label' => 'Published Courses', 'value' => number_format($stats['published_courses']), 'sub' => $stats['draft_courses'].' drafts'],
        ['label' => 'Total Enrolments', 'value' => number_format($stats['total_enrolments']), 'sub' => 'Across all courses'],
        ['label' => 'Total Revenue', 'value' => '$'.number_format($stats['total_revenue'], 2), 'sub' => $stats['total_payments'].' successful payments'],
    ] as $stat)
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-gray-400 mt-2">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">User Breakdown</h2>
        <div class="space-y-4">
            @foreach([
                ['label' => 'Students', 'count' => $stats['students'], 'color' => 'bg-blue-400'],
                ['label' => 'Instructors', 'count' => $stats['instructors'], 'color' => 'bg-purple-400'],
            ] as $item)
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">{{ $item['label'] }}</span>
                    <span class="text-sm font-bold">{{ number_format($item['count']) }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ $item['color'] }} h-full rounded-full" style="width: {{ $stats['total_users'] > 0 ? ($item['count'] / $stats['total_users'] * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Courses by Category</h2>
        <div class="space-y-3">
            @foreach($categoryBreakdown as $category)
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100">
                <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                <span class="text-sm text-gray-500">{{ $category->courses_count }} courses</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Top Courses by Enrolment</h2>
        <div class="overflow-x-auto">
            <table class="w-full dashboard-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th>Enrolments</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCourses as $course)
                    <tr>
                        <td class="font-semibold text-gray-900">{{ $course->title }}</td>
                        <td class="text-gray-500">{{ $course->instructor?->name ?? '—' }}</td>
                        <td>
                            <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td class="text-gray-500">{{ number_format($course->enrolments_count) }}</td>
                        <td class="font-semibold">${{ number_format($course->price, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Recent Payments</h2>
        <div class="overflow-x-auto">
            <table class="w-full dashboard-table">
                <thead>
                    <tr><th>Reference</th><th>Student</th><th>Course</th><th>Method</th><th>Amount</th><th>Paid at</th></tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td class="font-mono text-xs text-gray-500">{{ $payment->reference }}</td>
                        <td class="text-gray-700">{{ $payment->enrolment?->student?->name }}</td>
                        <td class="font-semibold text-gray-900">{{ $payment->enrolment?->course?->title }}</td>
                        <td class="text-gray-500">{{ $payment->method === 'card' ? ($payment->card_brand.' •••• '.$payment->card_last4) : ucfirst($payment->method) }}</td>
                        <td class="font-semibold">${{ number_format($payment->amount, 2) }}</td>
                        <td class="text-gray-500">{{ $payment->paid_at?->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-6">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection