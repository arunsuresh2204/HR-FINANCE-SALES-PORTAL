<x-app-layout>
    <x-slot name="header">Monthly Sales Report</x-slot>

    <livewire:sales.target-report :user="$user" :year="$year" :month="$month" />
</x-app-layout>
