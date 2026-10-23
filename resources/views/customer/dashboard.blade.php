<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">
            Book a Ride
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 rounded-2xl bg-gray-900 p-8 text-white">
                <p class="text-sm text-gray-300">Your next journey starts here</p>
                <h1 class="mt-2 text-3xl font-bold">
                    Where would you like to go?
                </h1>
                <p class="mt-2 text-gray-300">
                    Book a comfortable ride in just a few steps.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">

                <div class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 class="mb-5 text-xl font-bold">Plan your ride</h2>

                    <form>
                        <label class="mb-2 block text-sm font-medium">
                            Pickup location
                        </label>
                        <input type="text"
                            placeholder="Enter pickup location"
                            class="mb-5 w-full rounded-xl border-gray-300">

                        <label class="mb-2 block text-sm font-medium">
                            Destination
                        </label>
                        <input type="text"
                            placeholder="Where are you going?"
                            class="mb-5 w-full rounded-xl border-gray-300">

                        <button type="button"
                            class="w-full rounded-xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">
                            Find a Ride
                        </button>
                    </form>

                    <p class="mt-3 text-xs text-gray-500">
                        Booking functionality will be connected next.
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold">Your activity</h2>

                    <div class="mt-5 rounded-xl bg-green-50 p-4">
                        <p class="text-sm text-gray-600">Total rides</p>
                        <p class="mt-1 text-3xl font-bold text-green-700">0</p>
                    </div>

                    <div class="mt-4 rounded-xl bg-gray-50 p-4">
                        <p class="text-sm text-gray-600">Current ride</p>
                        <p class="mt-1 font-semibold">No active ride</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>