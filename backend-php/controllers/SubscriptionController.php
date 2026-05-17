<?php
// Subscription Controller

class SubscriptionController {
    private static $plans = [
        'premium' => ['label' => 'Premium', 'amount' => 9.99],
        'student' => ['label' => 'Student', 'amount' => 4.99],
    ];

    private static function ensurePaymentColumns() {
        $columns = [
            'Status' => 'ALTER TABLE payment ADD COLUMN Status varchar(20) DEFAULT "success"',
            'CardBrand' => 'ALTER TABLE payment ADD COLUMN CardBrand varchar(30) DEFAULT NULL',
            'CardLast4' => 'ALTER TABLE payment ADD COLUMN CardLast4 varchar(4) DEFAULT NULL',
            'TransactionRef' => 'ALTER TABLE payment ADD COLUMN TransactionRef varchar(64) DEFAULT NULL',
        ];

        foreach ($columns as $sql) {
            try {
                dbQuery($sql);
            } catch (Exception $e) {
                // Column already exists, or the active schema differs.
            }
        }
    }

    private static function normalizeCardNumber($number) {
        return preg_replace('/\D+/', '', $number ?? '');
    }

    private static function cardBrand($number) {
        if (preg_match('/^4/', $number)) return 'Visa';
        if (preg_match('/^(5[1-5]|2[2-7])/', $number)) return 'Mastercard';
        if (preg_match('/^3[47]/', $number)) return 'American Express';
        if (preg_match('/^6(?:011|5)/', $number)) return 'Discover';
        return 'Card';
    }

    public static function status() {
        $user = JWT::required();

        try {
            $sub = dbFetch(
                'SELECT SubID as id,
                        UserID as user_id,
                        Type as plan_type,
                        CASE WHEN PaymentStatus = "Paid" THEN "active" ELSE LOWER(COALESCE(PaymentStatus, "free")) END as status,
                        PaymentStatus as payment_status,
                        StartDate as start_date,
                        EndDate as end_date
                 FROM subscription
                 WHERE UserID = ?
                 ORDER BY SubID DESC
                 LIMIT 1',
                [$user['id']]
            );

            echo json_encode($sub ?: ['plan_type' => 'Free', 'status' => 'free']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function subscribe() {
        $user = JWT::required();
        $data = json_decode(file_get_contents('php://input'), true);
        $planKey = strtolower(trim($data['plan_type'] ?? 'premium'));
        $payment = $data['payment'] ?? [];

        if (!isset(self::$plans[$planKey])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid subscription plan']);
            return;
        }

        $cardName = trim($payment['card_name'] ?? '');
        $cardNumber = trim($payment['card_number'] ?? '');
        $expiry = trim($payment['expiry'] ?? '');
        $cvv = trim($payment['cvv'] ?? '');

        if (!$cardName || !$cardNumber || !$expiry || !$cvv) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter card payment details']);
            return;
        }

        try {
            self::ensurePaymentColumns();

            $plan = self::$plans[$planKey];
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+30 days'));
            $transactionRef = 'txn_' . bin2hex(random_bytes(12));
            $normalizedCardNumber = self::normalizeCardNumber($cardNumber);
            $brand = $normalizedCardNumber ? self::cardBrand($normalizedCardNumber) : 'Demo Card';
            $safeCardText = preg_replace('/\s+/', '', $cardNumber);
            $last4 = substr(str_pad($safeCardText, 4, '0', STR_PAD_LEFT), -4);

            dbQuery(
                'INSERT INTO subscription (UserID, Type, StartDate, EndDate, PaymentStatus) VALUES (?, ?, ?, ?, ?)',
                [$user['id'], $plan['label'], $startDate, $endDate, 'Paid']
            );

            dbQuery(
                'INSERT INTO payment (UserID, Amount, Date, Method, Status, CardBrand, CardLast4, TransactionRef)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$user['id'], $plan['amount'], $startDate, 'Card', 'success', $brand, $last4, $transactionRef]
            );

            try {
                dbQuery('UPDATE `user` SET SubscriptionType = ? WHERE UserID = ?', [$plan['label'], $user['id']]);
            } catch (Exception $e) {
                // Some installs only use the `users` table.
            }

            echo json_encode([
                'ok' => true,
                'plan' => $plan['label'],
                'amount' => $plan['amount'],
                'payment' => [
                    'status' => 'success',
                    'method' => 'Card',
                    'card_brand' => $brand,
                    'card_last4' => $last4,
                    'transaction_ref' => $transactionRef,
                ],
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
