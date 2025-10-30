<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $bookingData = [
            [
                'id' => 1,
                'name' => 'Ram Kailash',
                'phone' => '9905598912',
                'bookingId' => 'SDK89635',
                'nights' => 2,
                'roomType' => '1 King Room',
                'guests' => 2,
                'paid' => 'rsp.150',
                'cost' => 'rsp.1500',
            ],
            [
                'id' => 2,
                'name' => 'Samira Karki',
                'phone' => '9815394203',
                'bookingId' => 'SDK89635',
                'nights' => 4,
                'roomType' => ['1 Queen', '1 King Room'],
                'guests' => 5,
                'paid' => 'paid',
                'cost' => 'rsp.5500',
            ],
            [
                'id' => 3,
                'name' => 'Jeevan Rai',
                'phone' => '9865328452',
                'bookingId' => 'SDK89635',
                'nights' => 1,
                'roomType' => ['1 Deluxe', '1 King Room'],
                'guests' => 3,
                'paid' => 'rsp.150',
                'cost' => 'rsp.2500',
            ],
            [
                'id' => 4,
                'name' => 'Bindu Sharma',
                'phone' => '9845653124',
                'bookingId' => 'SDK89635',
                'nights' => 3,
                'roomType' => ['1 Deluxe', '1 King Room'],
                'guests' => 2,
                'paid' => 'rsp.150',
                'cost' => 'rsp.3000',
            ],
        ];

        $foodOrders = [
            [
                'id' => 'FO-1234',
                'guest' => 'Ram Kailash',
                'room' => '101',
                'items' => ['Chicken Curry', 'Naan Bread', 'Rice'],
                'total' => 'rsp.850',
                'status' => 'Delivered',
                'time' => '12:30 PM',
            ],
            [
                'id' => 'FO-1235',
                'guest' => 'Samira Karki',
                'room' => '205',
                'items' => ['Vegetable Pasta', 'Garlic Bread', 'Tiramisu'],
                'total' => 'rsp.1200',
                'status' => 'Preparing',
                'time' => '1:15 PM',
            ],
            [
                'id' => 'FO-1236',
                'guest' => 'Jeevan Rai',
                'room' => '310',
                'items' => ['Club Sandwich', 'French Fries', 'Coke'],
                'total' => 'rsp.650',
                'status' => 'On the way',
                'time' => '1:45 PM',
            ],
        ];

        $invoices = [
            [
                'id' => 'INV-2023-001',
                'guest' => 'Ram Kailash',
                'date' => '26 Jul 2023',
                'amount' => 'rsp.1500',
                'status' => 'Paid',
            ],
            [
                'id' => 'INV-2023-002',
                'guest' => 'Samira Karki',
                'date' => '25 Jul 2023',
                'amount' => 'rsp.5500',
                'status' => 'Paid',
            ],
            [
                'id' => 'INV-2023-003',
                'guest' => 'Jeevan Rai',
                'date' => '24 Jul 2023',
                'amount' => 'rsp.2500',
                'status' => 'Pending',
            ],
        ];

        return view('dashboard', compact('bookingData', 'foodOrders', 'invoices'));
    }
}
