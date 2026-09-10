<x-app-layout>
    <x-slot name="header">Financial Report</x-slot>

    <livewire:finance.financial-report-month :year="$year" :month="$month" />
</x-app-layout>
