<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Helpers\PhoneHelper;
use Livewire\Component;

class StoreSettings extends Component
{
    // Settings fields
    public string $whatsapp_number = '';
    public bool $show_visitor_counter = true;
    public bool $show_recent_orders = true;
    public int $shipping_cost = 200;
    public int $free_shipping_threshold = 2000;
    public int $slider_autoplay_duration = 5;
    public int $order_expiration_hours = 24;
    public string $store_email = '';
    public string $store_phone = '';
    public string $store_city = '';

    public function mount(): void
    {
        $this->whatsapp_number          = Setting::get('whatsapp_number', '03002922584');
        $this->show_visitor_counter      = (bool) Setting::get('show_visitor_counter', 1);
        $this->show_recent_orders        = (bool) Setting::get('show_recent_orders', 1);
        $this->shipping_cost             = (int) Setting::get('shipping_cost', 200);
        $this->free_shipping_threshold   = (int) Setting::get('free_shipping_threshold', 2000);
        $this->slider_autoplay_duration  = (int) Setting::get('slider_autoplay_duration', 5);
        $this->order_expiration_hours    = (int) Setting::get('order_expiration_hours', 24);
        $this->store_email               = Setting::get('store_email', 'contact@capitalcart.pk');
        $this->store_phone               = Setting::get('store_phone', '03002922584');
        $this->store_city                = Setting::get('store_city', 'Islamabad');
    }

    public function saveSettings(): void
    {
        // Custom Pakistani phone validation
        if (!PhoneHelper::isValidPakistaniNumber($this->whatsapp_number)) {
            $this->addError('whatsapp_number', 'Please enter a valid Pakistani mobile number (e.g. 03002922584 or +923002922584).');
            return;
        }

        $this->validate([
            'whatsapp_number'         => 'required|string',
            'show_visitor_counter'     => 'boolean',
            'show_recent_orders'       => 'boolean',
            'shipping_cost'            => 'required|integer|min:0|max:10000',
            'free_shipping_threshold'  => 'required|integer|min:0|max:100000',
            'slider_autoplay_duration' => 'required|integer|min:2|max:30',
            'order_expiration_hours'   => 'required|integer|min:1|max:168',
            'store_email'              => 'required|email|max:100',
            'store_phone'              => 'required|string|max:30',
            'store_city'               => 'required|string|max:100',
        ]);

        // Normalize WhatsApp number to standard local format before storing: e.g. 03002922584
        $normalizedNumber = PhoneHelper::toLocal($this->whatsapp_number);

        Setting::set('whatsapp_number', $normalizedNumber, 'whatsapp');
        Setting::set('show_visitor_counter', $this->show_visitor_counter ? '1' : '0', 'storefront');
        Setting::set('show_recent_orders', $this->show_recent_orders ? '1' : '0', 'storefront');
        Setting::set('shipping_cost', (string) $this->shipping_cost, 'shipping');
        Setting::set('free_shipping_threshold', (string) $this->free_shipping_threshold, 'shipping');
        Setting::set('slider_autoplay_duration', (string) $this->slider_autoplay_duration, 'storefront');
        Setting::set('order_expiration_hours', (string) $this->order_expiration_hours, 'orders');
        Setting::set('store_email', $this->store_email, 'general');
        Setting::set('store_phone', $this->store_phone, 'general');
        Setting::set('store_city', $this->store_city, 'general');

        $this->whatsapp_number = $normalizedNumber;

        $this->dispatch('show-toast', message: 'All settings saved successfully!', type: 'success');
    }

    public function render()
    {
        $internationalFormat = PhoneHelper::toInternational($this->whatsapp_number);

        return view('livewire.admin.settings.store-settings', [
            'internationalFormat' => $internationalFormat,
        ])->layout('layouts.admin', ['title' => 'Store Settings']);
    }
}
