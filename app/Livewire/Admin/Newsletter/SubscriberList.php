<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterSubscriber;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);

        if ($subscriber->status === 'active') {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            $this->dispatch('show-toast', message: 'Subscriber deactivated.', type: 'info');
        } else {
            $subscriber->update([
                'status' => 'active',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            $this->dispatch('show-toast', message: 'Subscriber reactivated.', type: 'success');
        }
    }

    public function deleteSubscriber(int $id): void
    {
        NewsletterSubscriber::findOrFail($id)->delete();
        $this->dispatch('show-toast', message: 'Subscriber removed.', type: 'success');
    }

    public function exportCsv(): StreamedResponse
    {
        $query = NewsletterSubscriber::query();

        if ($this->search) {
            $query->where('email', 'like', "%{$this->search}%");
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $subscribers = $query->latest('subscribed_at')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="capitalcart_subscribers_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Email', 'Status', 'Subscribed At', 'Unsubscribed At', 'Created At']);

            foreach ($subscribers as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->email,
                    $row->status,
                    $row->subscribed_at?->toDateTimeString() ?? '',
                    $row->unsubscribed_at?->toDateTimeString() ?? '',
                    $row->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function render()
    {
        $stats = [
            'total'        => NewsletterSubscriber::count(),
            'active'       => NewsletterSubscriber::where('status', 'active')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
            'this_month'   => NewsletterSubscriber::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        $query = NewsletterSubscriber::query();

        if ($this->search) {
            $query->where('email', 'like', "%{$this->search}%");
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $subscribers = $query->latest()->paginate(15);

        return view('livewire.admin.newsletter.subscriber-list', [
            'subscribers' => $subscribers,
            'stats'       => $stats,
        ])->layout('layouts.admin', ['title' => 'Newsletter Subscribers']);
    }
}
