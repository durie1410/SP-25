<?php

return [
	// Rental fee per book (flat, in VND) for MVP
	'rental_flat' => env('LIB_RENTAL_FLAT', 10000),

	// Rental fee daily rate (percentage of book price per day)
	'rental_daily_rate' => env('LIB_RENTAL_DAILY_RATE', 0.01), // 1% of book price per day
	'rental_daily_rate_with_card' => env('LIB_RENTAL_DAILY_RATE_WITH_CARD', 0.005), // 0.5% of book price per day (with reader card)

	// Deposit per book depending on KYC status (in VND)
	'deposit_verified' => env('LIB_DEPOSIT_VERIFIED', 50000),
	'deposit_unverified' => env('LIB_DEPOSIT_UNVERIFIED', 100000),

	// Loan duration and penalties
	'loan_days' => env('LIB_LOAN_DAYS', 7),
	'late_fee_per_day' => env('LIB_LATE_FEE_PER_DAY', 2000),

	// Shipping & reservations operational configs
	'ship_fee_default' => env('LIB_SHIP_FEE_DEFAULT', 15000),
	'reservation_hold_minutes' => env('LIB_RESERVATION_HOLD_MINUTES', 3),

	// SLA / opening hours (informational; use in UI/validation as needed)
	'open_hour' => env('LIB_OPEN_HOUR', '08:00'),
	'close_hour' => env('LIB_CLOSE_HOUR', '18:00'),

	// Borrow rules
	'borrow_min_days' => env('LIB_BORROW_MIN_DAYS', 1),
	'borrow_max_days' => env('LIB_BORROW_MAX_DAYS', 14),
	'borrow_min_books' => env('LIB_BORROW_MIN_BOOKS', 1),
	'borrow_max_books' => env('LIB_BORROW_MAX_BOOKS', 5),
	'capacity_target_ratio' => env('LIB_CAPACITY_TARGET_RATIO', 1.1), // e.g., 50 seats -> 55/h

	// Notification timings (days)
	'reminder_due_soon_days' => env('LIB_REMINDER_DUE_SOON_DAYS', 1),
	'reminder_overdue_days' => env('LIB_REMINDER_OVERDUE_DAYS', 0),
];


