<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Platform Overview
                </h1>
                <p class="mt-2 text-gray-500">
                    Manage drivers, customers and bookings.
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Total customers', '0'],
                    ['Total drivers', '0'],
                    ['Total rides', '0'],
                    ['Pending approvals', '0']
                ] as [$label, $value])
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ $value }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold">Recent rides</h2>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 text-gray-500">
                            <tr>
                                <th class="p-4">Ride ID</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Driver</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4"
                                    class="p-8 text-center text-gray-500">
                                    No rides available yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold">Driver approvals</h2>
                <p class="mt-3 text-sm text-gray-500">
                    Pending driver applications will appear here.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>