<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Driver Dashboard</h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-2xl bg-gray-900 p-8 text-white">
                <p class="text-gray-300">Welcome back, Driver</p>
                <h1 class="mt-2 text-3xl font-bold">Ready for your next ride?</h1>
                <p class="mt-2 text-gray-300">
                    Manage your availability and ride requests.
                </p>

                <div class="mt-6">
                    <span class="rounded-full bg-gray-700 px-4 py-2 text-sm">
                        Currently Offline
                    </span>
                </div>

                <button type="button"
                    class="mt-5 rounded-xl bg-green-500 px-6 py-3 font-semibold text-white">
                    Go Online
                </button>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ([
                    ['Today earnings', '₹0'],
                    ['Completed rides', '0'],
                    ['Pending requests', '0']
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
                <h2 class="text-xl font-bold">Ride requests</h2>
                <div class="mt-5 rounded-xl border border-dashed border-gray-300 p-10 text-center">
                    <p class="font-semibold text-gray-700">
                        No ride requests yet
                    </p>
                    <p class="mt-2 text-sm text-gray-500">
                        New requests will appear here when you are online.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>