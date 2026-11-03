<?php
declare(strict_types=1);

namespace App\Services\Reports;

use App\Core\Database;

/**
 * Admin > Reports: the catalogue of every report of the website, one entry each. A report is a SELECT whose column aliases are the keys of 'cols'
 * (label = column heading), an ability that decides who sees it (the same ability that opens the area the data belongs to), whether the date range
 * applies ('range'), and the columns to total ('sum'). The controller adds paging / LIMIT, totals and the CSV download, so an entry only holds its SQL:
 *
 *   'sql' => fn(array $r): array => [$sql, $params]        $r = ['from' => ?string, 'to' => ?string] (datetime strings; 'to' is exclusive)
 *
 * Add a report = add one line to self::all(); the self-test (bin/selftest-reports.php) runs every one of them.
 */
final class ReportCatalog
{
    public const PERIODS = ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last 12 months', 'all' => 'All time', 'custom' => 'Custom dates'];

    public const GROUPS = [
        'donations'  => ['Donations', 'heart'],
        'people'     => ['Messages and people', 'mail'],
        'membership' => ['Membership', 'users'],
        'shop'       => ['Shop', 'shopping-bag'],
        'academy'    => ['LAF Academy', 'graduation-cap'],
        'newsletter' => ['Newsletter and social media', 'send'],
        'traffic'    => ['Website traffic', 'globe'],
        'content'    => ['Website content', 'layout'],
        'meetings'   => ['Events, calendar and meetings', 'calendar'],
        'security'   => ['Security and administration', 'shield'],
    ];

    /** @return array{from:?string,to:?string,period:string,fromDate:string,toDate:string} */
    public static function range(string $period, string $from, string $to): array
    {
        $period = isset(self::PERIODS[$period]) ? $period : '30';
        $out = ['from' => null, 'to' => null, 'period' => $period, 'fromDate' => '', 'toDate' => ''];
        if ($period === 'custom') {
            $f = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && strtotime($from) !== false ? $from : '';
            $t = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strtotime($to) !== false ? $to : '';
            $out['fromDate'] = $f;
            $out['toDate'] = $t;
            $out['from'] = $f !== '' ? $f . ' 00:00:00' : null;
            $out['to'] = $t !== '' ? date('Y-m-d 00:00:00', strtotime($t . ' +1 day')) : null;
        } elseif ($period !== 'all') {
            $out['from'] = date('Y-m-d H:i:s', strtotime('-' . (int) $period . ' day'));
        }
        return $out;
    }

    /** " AND col >= ? AND col < ?" for the range. @param array{from:?string,to:?string} $r @return array{0:string,1:list<string>} */
    public static function rng(array $r, string $col): array
    {
        $w = '';
        $p = [];
        if ($r['from'] !== null) {
            $w .= " AND $col >= ?";
            $p[] = $r['from'];
        }
        if ($r['to'] !== null) {
            $w .= " AND $col < ?";
            $p[] = $r['to'];
        }
        return [$w, $p];
    }

    /** @return array<string,array<string,mixed>> key => report */
    public static function all(): array
    {
        static $all = null;
        if ($all !== null) {
            return $all;
        }
        $list = [];
        $add = static function (string $group, string $key, string $title, string $desc, string $ability, array $cols, \Closure $sql, array $sum = [], bool $range = true) use (&$list): void {
            $list[$key] = ['key' => $key, 'group' => $group, 'title' => $title, 'desc' => $desc, 'ability' => $ability, 'cols' => $cols, 'sql' => $sql, 'sum' => $sum, 'range' => $range];
        };
        $rng = [self::class, 'rng'];

        // ============================================================================================================== DONATIONS
        $A = 'inbox.manage';
        $add('donations', 'donations-list', 'All donations', 'Every donation in the period: who, how much, how, and its status.', $A,
            ['created_at' => 'Date', 'donor' => 'Donor', 'donor_email' => 'E-mail', 'amount' => 'Amount', 'currency' => 'Currency', 'method' => 'Method', 'status' => 'Status', 'source' => 'Source', 'reference' => 'Reference', 'receipt_no' => 'Receipt'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT d.created_at, IF(d.is_anonymous = 1, 'Anonymous', d.donor_name) AS donor, d.donor_email, d.amount, d.currency, d.method, d.status, d.source, d.reference, d.receipt_no FROM donations d WHERE 1 = 1$w ORDER BY d.created_at DESC", $p];
            }, ['amount']);
        $add('donations', 'donations-by-month', 'Donations by month', 'Number and total of donations received each month, per currency.', $A,
            ['month' => 'Month', 'currency' => 'Currency', 'donations' => 'Donations', 'total' => 'Total received'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT DATE_FORMAT(d.created_at, '%Y-%m') AS month, d.currency, COUNT(*) AS donations, SUM(IF(d.status = 'received', d.amount, 0)) AS total FROM donations d WHERE 1 = 1$w GROUP BY month, d.currency ORDER BY month DESC", $p];
            }, ['donations', 'total']);
        $add('donations', 'donations-by-method', 'Donations by payment method', 'Which payment methods donors use.', $A,
            ['method' => 'Method', 'currency' => 'Currency', 'donations' => 'Donations', 'total' => 'Total received'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT COALESCE(NULLIF(d.method, ''), '(not stated)') AS method, d.currency, COUNT(*) AS donations, SUM(IF(d.status = 'received', d.amount, 0)) AS total FROM donations d WHERE 1 = 1$w GROUP BY method, d.currency ORDER BY donations DESC", $p];
            }, ['donations', 'total']);
        $add('donations', 'donations-by-status', 'Donations by status', 'Pledged, received and cancelled donations.', $A,
            ['status' => 'Status', 'currency' => 'Currency', 'donations' => 'Donations', 'total' => 'Amount'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT d.status, d.currency, COUNT(*) AS donations, SUM(d.amount) AS total FROM donations d WHERE 1 = 1$w GROUP BY d.status, d.currency ORDER BY donations DESC", $p];
            }, ['donations', 'total']);
        $add('donations', 'donations-by-source', 'Donations by source', 'Where donations came from (website, manual entry, campaign).', $A,
            ['source' => 'Source', 'donations' => 'Donations', 'total' => 'Received'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT COALESCE(NULLIF(d.source, ''), '(not stated)') AS source, COUNT(*) AS donations, SUM(IF(d.status = 'received', d.amount, 0)) AS total FROM donations d WHERE 1 = 1$w GROUP BY source ORDER BY donations DESC", $p];
            }, ['donations', 'total']);
        $add('donations', 'donations-top-donors', 'Top donors', 'Donors who gave most in the period (anonymous gifts left out).', $A,
            ['donor' => 'Donor', 'email' => 'E-mail', 'donations' => 'Donations', 'total' => 'Total received', 'last_gift' => 'Last gift'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT d.donor_name AS donor, d.donor_email AS email, COUNT(*) AS donations, SUM(d.amount) AS total, MAX(d.created_at) AS last_gift FROM donations d WHERE d.is_anonymous = 0 AND d.status = 'received'$w GROUP BY d.donor_email, d.donor_name ORDER BY total DESC", $p];
            }, ['donations', 'total']);
        $add('donations', 'donations-pledged-open', 'Pledges not yet received', 'Donations still waiting to be received: follow these up.', $A,
            ['created_at' => 'Pledged on', 'donor' => 'Donor', 'donor_email' => 'E-mail', 'donor_phone' => 'Phone', 'amount' => 'Amount', 'currency' => 'Currency', 'method' => 'Method'],
            static fn(array $r): array => ["SELECT d.created_at, d.donor_name AS donor, d.donor_email, d.donor_phone, d.amount, d.currency, d.method FROM donations d WHERE d.status = 'pledged' ORDER BY d.created_at DESC", []],
            ['amount'], false);

        // ============================================================================================================== MESSAGES AND PEOPLE
        $add('people', 'contact-list', 'Contact messages', 'Every message sent through the contact form.', $A,
            ['created_at' => 'Date', 'name' => 'Name', 'email' => 'E-mail', 'phone' => 'Phone', 'subject' => 'Subject', 'status' => 'Status', 'replied_at' => 'Replied'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'c.created_at');
                return ["SELECT c.created_at, c.name, c.email, c.phone, c.subject, c.status, c.replied_at FROM contact_messages c WHERE 1 = 1$w ORDER BY c.created_at DESC", $p];
            });
        $add('people', 'contact-by-month', 'Contact messages by month', 'How many messages arrive each month and how many were answered.', $A,
            ['month' => 'Month', 'messages' => 'Messages', 'replied' => 'Replied', 'unanswered' => 'Not replied'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'c.created_at');
                return ["SELECT DATE_FORMAT(c.created_at, '%Y-%m') AS month, COUNT(*) AS messages, SUM(c.replied_at IS NOT NULL) AS replied, SUM(c.replied_at IS NULL) AS unanswered FROM contact_messages c WHERE 1 = 1$w GROUP BY month ORDER BY month DESC", $p];
            }, ['messages', 'replied', 'unanswered']);
        $add('people', 'contact-unanswered', 'Messages still waiting for a reply', 'Messages nobody has replied to yet, oldest first.', $A,
            ['created_at' => 'Received', 'days' => 'Days waiting', 'name' => 'Name', 'email' => 'E-mail', 'subject' => 'Subject'],
            static fn(array $r): array => ["SELECT c.created_at, DATEDIFF(NOW(), c.created_at) AS days, c.name, c.email, c.subject FROM contact_messages c WHERE c.replied_at IS NULL ORDER BY c.created_at ASC", []],
            [], false);
        $add('people', 'subscribers-list', 'Newsletter subscribers', 'Everyone on the list in the period, with how they joined.', $A,
            ['subscribed_at' => 'Joined', 'email' => 'E-mail', 'name' => 'Name', 'status' => 'Status', 'source' => 'Source', 'unsubscribed_at' => 'Left'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.subscribed_at');
                return ["SELECT s.subscribed_at, s.email, s.name, s.status, s.source, s.unsubscribed_at FROM subscribers s WHERE 1 = 1$w ORDER BY s.subscribed_at DESC", $p];
            });
        $add('people', 'subscribers-growth', 'Subscriber growth by month', 'New subscribers and people who left, month by month.', $A,
            ['month' => 'Month', 'joined' => 'Joined', 'left' => 'Left'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.subscribed_at');
                return ["SELECT DATE_FORMAT(s.subscribed_at, '%Y-%m') AS month, COUNT(*) AS joined, SUM(s.unsubscribed_at IS NOT NULL) AS `left` FROM subscribers s WHERE 1 = 1$w GROUP BY month ORDER BY month DESC", $p];
            }, ['joined', 'left']);
        $add('people', 'subscribers-by-source', 'Subscribers by source', 'Where subscribers signed up (footer form, popup, import ...).', $A,
            ['source' => 'Source', 'status' => 'Status', 'subscribers' => 'Subscribers'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.subscribed_at');
                return ["SELECT COALESCE(NULLIF(s.source, ''), '(not stated)') AS source, s.status, COUNT(*) AS subscribers FROM subscribers s WHERE 1 = 1$w GROUP BY source, s.status ORDER BY subscribers DESC", $p];
            }, ['subscribers']);
        $add('people', 'involvement-list', 'Involvement requests', 'Volunteer, partner and other requests to get involved.', $A,
            ['created_at' => 'Date', 'name' => 'Name', 'email' => 'E-mail', 'phone' => 'Phone', 'involvement_type' => 'Type', 'organisation' => 'Organisation', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'i.created_at');
                return ["SELECT i.created_at, i.name, i.email, i.phone, i.involvement_type, i.organisation, i.status FROM involvement_requests i WHERE 1 = 1$w ORDER BY i.created_at DESC", $p];
            });
        $add('people', 'involvement-by-type', 'Involvement requests by type', 'What people offer: volunteering, partnership, sponsorship ...', $A,
            ['involvement_type' => 'Type', 'status' => 'Status', 'requests' => 'Requests'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'i.created_at');
                return ["SELECT i.involvement_type, i.status, COUNT(*) AS requests FROM involvement_requests i WHERE 1 = 1$w GROUP BY i.involvement_type, i.status ORDER BY requests DESC", $p];
            }, ['requests']);
        $add('people', 'applications-list', 'Job applications', 'Applications received for each job.', $A,
            ['created_at' => 'Date', 'job' => 'Job', 'name' => 'Applicant', 'email' => 'E-mail', 'phone' => 'Phone', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT a.created_at, COALESCE(j.title, '(job removed)') AS job, a.name, a.email, a.phone, a.status FROM applications a LEFT JOIN jobs j ON j.id = a.job_id WHERE 1 = 1$w ORDER BY a.created_at DESC", $p];
            });
        $add('people', 'applications-by-job', 'Applications per job', 'How many people applied for each job, and how many are still new.', $A,
            ['job' => 'Job', 'applications' => 'Applications', 'new_ones' => 'Still new', 'last_application' => 'Latest'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT COALESCE(j.title, '(job removed)') AS job, COUNT(*) AS applications, SUM(a.status = 'new') AS new_ones, MAX(a.created_at) AS last_application FROM applications a LEFT JOIN jobs j ON j.id = a.job_id WHERE 1 = 1$w GROUP BY job ORDER BY applications DESC", $p];
            }, ['applications', 'new_ones']);
        $add('people', 'registrations-list', 'Event registrations', 'Everyone who registered for an event.', $A,
            ['created_at' => 'Registered', 'event' => 'Event', 'name' => 'Name', 'email' => 'E-mail', 'phone' => 'Phone', 'organisation' => 'Organisation', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'g.created_at');
                return ["SELECT g.created_at, COALESCE(e.title, '(event removed)') AS event, g.name, g.email, g.phone, g.organisation, g.status FROM event_registrations g LEFT JOIN events e ON e.id = g.event_id WHERE 1 = 1$w ORDER BY g.created_at DESC", $p];
            });
        $add('people', 'registrations-by-event', 'Registrations per event', 'Registrations against capacity for each event.', $A,
            ['event' => 'Event', 'start_at' => 'Starts', 'capacity' => 'Capacity', 'registrations' => 'Registrations'],
            static fn(array $r): array => ["SELECT e.title AS event, e.start_at, e.capacity, COUNT(g.id) AS registrations FROM events e LEFT JOIN event_registrations g ON g.event_id = e.id GROUP BY e.id, e.title, e.start_at, e.capacity ORDER BY e.start_at DESC", []],
            ['registrations'], false);
        $add('people', 'ambassadors-list', 'Ambassador applications', 'People who applied to be ambassadors, with their pledge.', $A,
            ['created_at' => 'Date', 'name' => 'Name', 'email' => 'E-mail', 'country' => 'Country', 'city' => 'City', 'annual_amount' => 'Annual pledge', 'currency' => 'Currency', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT a.created_at, a.name, a.email, a.country, a.city, a.annual_amount, a.currency, a.status FROM ambassador_applications a WHERE 1 = 1$w ORDER BY a.created_at DESC", $p];
            }, ['annual_amount']);
        $add('people', 'ambassadors-by-country', 'Ambassadors by country', 'Where ambassador applicants live.', $A,
            ['country' => 'Country', 'applicants' => 'Applicants', 'approved' => 'Approved'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT COALESCE(NULLIF(a.country, ''), '(not stated)') AS country, COUNT(*) AS applicants, SUM(a.status = 'approved') AS approved FROM ambassador_applications a WHERE 1 = 1$w GROUP BY country ORDER BY applicants DESC", $p];
            }, ['applicants', 'approved']);
        $add('people', 'forms-submissions', 'Form submissions', 'Every answer sent through the website forms.', 'forms.submissions',
            ['created_at' => 'Date', 'form' => 'Form', 'submitter_email' => 'E-mail', 'status' => 'Status', 'context_label' => 'Sent from'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.created_at');
                return ["SELECT s.created_at, COALESCE(f.title, '(form removed)') AS form, s.submitter_email, s.status, s.context_label FROM form_submissions s LEFT JOIN forms f ON f.id = s.form_id WHERE 1 = 1$w ORDER BY s.created_at DESC", $p];
            });
        $add('people', 'forms-by-form', 'Submissions per form', 'How many answers each form received.', 'forms.submissions',
            ['form' => 'Form', 'status' => 'Form status', 'submissions' => 'Submissions', 'latest' => 'Latest'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.created_at');
                return ["SELECT COALESCE(f.title, '(form removed)') AS form, COALESCE(f.status, '') AS status, COUNT(*) AS submissions, MAX(s.created_at) AS latest FROM form_submissions s LEFT JOIN forms f ON f.id = s.form_id WHERE 1 = 1$w GROUP BY s.form_id, form, f.status ORDER BY submissions DESC", $p];
            }, ['submissions']);

        // ============================================================================================================== MEMBERSHIP
        $A = 'members.manage';
        $add('membership', 'members-list', 'Members', 'Every membership application and member in the period.', $A,
            ['created_at' => 'Applied', 'membership_number' => 'Member no', 'full_name' => 'Name', 'type_name' => 'Type', 'status' => 'Status', 'country' => 'Country', 'city' => 'City', 'email' => 'E-mail', 'phone' => 'Phone'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT m.created_at, m.membership_number, m.full_name, m.type_name, m.status, m.country, m.city, m.email, m.phone FROM members m WHERE 1 = 1$w ORDER BY m.created_at DESC", $p];
            });
        $add('membership', 'members-by-status', 'Members by status', 'Pending, approved, rejected and expired members.', $A,
            ['status' => 'Status', 'members' => 'Members'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT m.status, COUNT(*) AS members FROM members m WHERE 1 = 1$w GROUP BY m.status ORDER BY members DESC", $p];
            }, ['members']);
        $add('membership', 'members-by-type', 'Members by membership type', 'How many members each type has, and the fees they carry.', $A,
            ['type_name' => 'Type', 'status' => 'Status', 'members' => 'Members'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT COALESCE(NULLIF(m.type_name, ''), '(no type)') AS type_name, m.status, COUNT(*) AS members FROM members m WHERE 1 = 1$w GROUP BY type_name, m.status ORDER BY members DESC", $p];
            }, ['members']);
        $add('membership', 'members-by-country', 'Members by country', 'Where members live.', $A,
            ['country' => 'Country', 'members' => 'Members'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT COALESCE(NULLIF(m.country, ''), '(not stated)') AS country, COUNT(*) AS members FROM members m WHERE 1 = 1$w GROUP BY country ORDER BY members DESC", $p];
            }, ['members']);
        $add('membership', 'members-by-gender', 'Members by gender', 'Gender profile of the membership.', $A,
            ['gender' => 'Gender', 'members' => 'Members'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT COALESCE(NULLIF(m.gender, ''), '(not stated)') AS gender, COUNT(*) AS members FROM members m WHERE 1 = 1$w GROUP BY gender ORDER BY members DESC", $p];
            }, ['members']);
        $add('membership', 'members-by-month', 'Membership applications by month', 'New applications each month.', $A,
            ['month' => 'Month', 'applications' => 'Applications', 'approved' => 'Approved'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT DATE_FORMAT(m.created_at, '%Y-%m') AS month, COUNT(*) AS applications, SUM(m.status = 'approved') AS approved FROM members m WHERE 1 = 1$w GROUP BY month ORDER BY month DESC", $p];
            }, ['applications', 'approved']);
        $add('membership', 'members-payments', 'Membership payments', 'Every membership payment and receipt.', $A,
            ['paid_at' => 'Paid', 'receipt_number' => 'Receipt', 'member' => 'Member', 'purpose' => 'For', 'amount' => 'Amount', 'currency' => 'Currency', 'method' => 'Method', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'p.created_at');
                return ["SELECT COALESCE(p.paid_at, p.created_at) AS paid_at, p.receipt_number, COALESCE(m.full_name, '(member removed)') AS member, p.purpose, p.amount, p.currency, p.method, p.status FROM member_payments p LEFT JOIN members m ON m.id = p.member_id WHERE 1 = 1$w ORDER BY paid_at DESC", $p];
            }, ['amount']);
        $add('membership', 'members-payments-by-month', 'Membership income by month', 'Money received from members each month.', $A,
            ['month' => 'Month', 'currency' => 'Currency', 'payments' => 'Payments', 'total' => 'Total'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'p.created_at');
                return ["SELECT DATE_FORMAT(COALESCE(p.paid_at, p.created_at), '%Y-%m') AS month, p.currency, COUNT(*) AS payments, SUM(p.amount) AS total FROM member_payments p WHERE p.status IN ('paid', 'received', 'confirmed')$w GROUP BY month, p.currency ORDER BY month DESC", $p];
            }, ['payments', 'total']);
        $add('membership', 'members-expiring', 'Memberships ending in the next 60 days', 'Members whose paid term ends soon: remind them to renew.', $A,
            ['member' => 'Member', 'email' => 'E-mail', 'phone' => 'Phone', 'term_end' => 'Term ends', 'days_left' => 'Days left'],
            static fn(array $r): array => ["SELECT m.full_name AS member, m.email, m.phone, MAX(p.term_end) AS term_end, DATEDIFF(MAX(p.term_end), CURDATE()) AS days_left FROM members m JOIN member_payments p ON p.member_id = m.id WHERE m.status = 'approved' AND p.term_end IS NOT NULL GROUP BY m.id, m.full_name, m.email, m.phone HAVING MAX(p.term_end) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) ORDER BY term_end", []],
            [], false);

        // ============================================================================================================== SHOP
        $A = 'shop.orders';
        $add('shop', 'shop-orders', 'Shop orders', 'Every order with its payment and delivery.', $A,
            ['created_at' => 'Date', 'order_number' => 'Order', 'customer_name' => 'Customer', 'customer_email' => 'E-mail', 'status' => 'Status', 'payment_status' => 'Payment', 'payment_method' => 'Paid by', 'delivery_method' => 'Delivery', 'total' => 'Total', 'currency' => 'Currency'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT o.created_at, o.order_number, o.customer_name, o.customer_email, o.status, o.payment_status, o.payment_method, o.delivery_method, o.total, o.currency FROM shop_orders o WHERE 1 = 1$w ORDER BY o.created_at DESC", $p];
            }, ['total']);
        $add('shop', 'shop-sales-by-month', 'Shop sales by month', 'Paid orders and revenue each month.', $A,
            ['month' => 'Month', 'currency' => 'Currency', 'orders' => 'Paid orders', 'revenue' => 'Revenue'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS month, o.currency, COUNT(*) AS orders, SUM(o.total) AS revenue FROM shop_orders o WHERE o.payment_status = 'paid'$w GROUP BY month, o.currency ORDER BY month DESC", $p];
            }, ['orders', 'revenue']);
        $add('shop', 'shop-by-status', 'Orders by status', 'Where orders are in the process.', $A,
            ['status' => 'Status', 'orders' => 'Orders', 'value' => 'Value'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT o.status, COUNT(*) AS orders, SUM(o.total) AS value FROM shop_orders o WHERE 1 = 1$w GROUP BY o.status ORDER BY orders DESC", $p];
            }, ['orders', 'value']);
        $add('shop', 'shop-by-payment', 'Orders by payment status and method', 'Paid, unpaid and failed orders, and how customers pay.', $A,
            ['payment_status' => 'Payment status', 'payment_method' => 'Method', 'orders' => 'Orders', 'value' => 'Value'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT o.payment_status, COALESCE(NULLIF(o.payment_method, ''), '(not chosen)') AS payment_method, COUNT(*) AS orders, SUM(o.total) AS value FROM shop_orders o WHERE 1 = 1$w GROUP BY o.payment_status, payment_method ORDER BY orders DESC", $p];
            }, ['orders', 'value']);
        $add('shop', 'shop-products-sold', 'Products sold', 'Units and revenue per product (paid orders).', $A,
            ['name' => 'Product', 'units' => 'Units', 'revenue' => 'Revenue'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT i.name, SUM(i.qty) AS units, SUM(i.line_total) AS revenue FROM shop_order_items i JOIN shop_orders o ON o.id = i.order_id WHERE o.payment_status = 'paid'$w GROUP BY i.name ORDER BY units DESC", $p];
            }, ['units', 'revenue']);
        $add('shop', 'shop-customers', 'Top customers', 'Customers by value of paid orders.', $A,
            ['customer' => 'Customer', 'email' => 'E-mail', 'orders' => 'Orders', 'spent' => 'Spent'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT o.customer_name AS customer, o.customer_email AS email, COUNT(*) AS orders, SUM(o.total) AS spent FROM shop_orders o WHERE o.payment_status = 'paid'$w GROUP BY o.customer_email, o.customer_name ORDER BY spent DESC", $p];
            }, ['orders', 'spent']);
        $add('shop', 'shop-stock', 'Stock levels', 'Every product with its stock, lowest first. Low stock is flagged.', 'shop.manage',
            ['name' => 'Product', 'sku' => 'SKU', 'status' => 'Status', 'price' => 'Price', 'stock_qty' => 'In stock', 'low_stock' => 'Low-stock level', 'alert' => 'Alert'],
            static fn(array $r): array => ["SELECT p.name, p.sku, p.status, p.price, p.stock_qty, p.low_stock, IF(p.track_stock = 1 AND p.stock_qty <= p.low_stock, 'LOW', '') AS alert FROM shop_products p WHERE p.track_stock = 1 ORDER BY p.stock_qty ASC", []],
            [], false);

        // ============================================================================================================== ACADEMY
        $A = 'academy.manage';
        $add('academy', 'academy-learners', 'Learners', 'Every learner in the period with their account status and last sign-in.', $A,
            ['created_at' => 'Joined', 'reg_no' => 'Reg no', 'name' => 'Name', 'email' => 'E-mail', 'country' => 'Country', 'approved' => 'Approved', 'last_login_at' => 'Last sign-in', 'fee_status' => 'Sign-up fee'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT l.created_at, l.reg_no, l.name, l.email, l.country, IF(l.email_verified_at IS NULL, 'no', 'yes') AS approved, l.last_login_at, l.fee_status FROM academy_learners l WHERE 1 = 1$w ORDER BY l.created_at DESC", $p];
            });
        $add('academy', 'academy-signups-by-month', 'Learner sign-ups by month', 'New accounts each month, how many were approved and how many ever signed in.', $A,
            ['month' => 'Month', 'signups' => 'Sign-ups', 'approved' => 'Approved', 'signed_in' => 'Signed in at least once'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT DATE_FORMAT(l.created_at, '%Y-%m') AS month, COUNT(*) AS signups, SUM(l.email_verified_at IS NOT NULL) AS approved, SUM(l.last_login_at IS NOT NULL) AS signed_in FROM academy_learners l WHERE 1 = 1$w GROUP BY month ORDER BY month DESC", $p];
            }, ['signups', 'approved', 'signed_in']);
        $add('academy', 'academy-signed-in', 'Learners who have signed in', 'Learners with at least one successful sign-in, most recent first.', $A,
            ['last_login_at' => 'Last sign-in', 'reg_no' => 'Reg no', 'name' => 'Name', 'email' => 'E-mail', 'country' => 'Country', 'courses' => 'Courses', 'lessons_done' => 'Lessons done'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.last_login_at');
                return ["SELECT l.last_login_at, l.reg_no, l.name, l.email, l.country, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, (SELECT COUNT(*) FROM academy_progress g WHERE g.learner_id = l.id) AS lessons_done FROM academy_learners l WHERE l.last_login_at IS NOT NULL$w ORDER BY l.last_login_at DESC", $p];
            }, ['courses', 'lessons_done']);
        $add('academy', 'academy-never-signed-in', 'Approved learners who never signed in', 'Approved accounts that have not signed in even once: send them a reminder.', $A,
            ['approved_at' => 'Approved', 'days' => 'Days since approval', 'reg_no' => 'Reg no', 'name' => 'Name', 'email' => 'E-mail', 'phone' => 'Phone', 'country' => 'Country'],
            static fn(array $r): array => ["SELECT l.approved_at, DATEDIFF(NOW(), COALESCE(l.approved_at, l.email_verified_at)) AS days, l.reg_no, l.name, l.email, l.phone, l.country FROM academy_learners l WHERE l.is_active = 1 AND l.email_verified_at IS NOT NULL AND l.last_login_at IS NULL ORDER BY l.approved_at ASC", []],
            [], false);
        $add('academy', 'academy-inactive', 'Learners not seen for 30 days', 'Learners who signed in once but not in the last 30 days.', $A,
            ['last_login_at' => 'Last sign-in', 'days' => 'Days away', 'name' => 'Name', 'email' => 'E-mail', 'courses' => 'Courses'],
            static fn(array $r): array => ["SELECT l.last_login_at, DATEDIFF(NOW(), l.last_login_at) AS days, l.name, l.email, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses FROM academy_learners l WHERE l.is_active = 1 AND l.last_login_at IS NOT NULL AND l.last_login_at < DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY l.last_login_at ASC", []],
            ['courses'], false);
        $add('academy', 'academy-no-picture', 'Approved learners without a picture', 'Approved, active learners who have not added a profile picture: remind them or switch them off after the deadline.', $A,
            ['approved_at' => 'Approved', 'reg_no' => 'Reg no', 'name' => 'Name', 'email' => 'E-mail', 'phone' => 'Phone', 'last_login_at' => 'Last sign-in'],
            static fn(array $r): array => ["SELECT l.approved_at, l.reg_no, l.name, l.email, l.phone, l.last_login_at FROM academy_learners l WHERE l.is_active = 1 AND l.email_verified_at IS NOT NULL AND l.photo_path IS NULL ORDER BY l.id", []],
            [], false);
        $add('academy', 'academy-by-country', 'Learners by country', 'Where learners come from.', $A,
            ['country' => 'Country', 'learners' => 'Learners', 'signed_in' => 'Signed in'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT COALESCE(NULLIF(l.country, ''), '(not stated)') AS country, COUNT(*) AS learners, SUM(l.last_login_at IS NOT NULL) AS signed_in FROM academy_learners l WHERE 1 = 1$w GROUP BY country ORDER BY learners DESC", $p];
            }, ['learners', 'signed_in']);
        $add('academy', 'academy-fee-status', 'Sign-up fee status', 'How many learners have paid, owe or were waived the sign-up fee.', $A,
            ['fee_status' => 'Fee status', 'learners' => 'Learners'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT l.fee_status, COUNT(*) AS learners FROM academy_learners l WHERE 1 = 1$w GROUP BY l.fee_status ORDER BY learners DESC", $p];
            }, ['learners']);
        $add('academy', 'academy-enrolments', 'Enrolments and completions per course', 'How many learners joined each course and how many finished it.', $A,
            ['course' => 'Course', 'category' => 'Category', 'enrolled' => 'Enrolled', 'completed' => 'Completed', 'completion_pct' => 'Completion %'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'e.enrolled_at');
                return ["SELECT c.title AS course, c.category, COUNT(*) AS enrolled, SUM(e.completed_at IS NOT NULL) AS completed, ROUND(100 * SUM(e.completed_at IS NOT NULL) / COUNT(*), 1) AS completion_pct FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id WHERE 1 = 1$w GROUP BY c.id, c.title, c.category ORDER BY enrolled DESC", $p];
            }, ['enrolled', 'completed']);
        $add('academy', 'academy-popular-categories', 'Enrolments by course category', 'Which subject areas learners choose.', $A,
            ['category' => 'Category', 'enrolled' => 'Enrolments', 'learners' => 'Different learners'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'e.enrolled_at');
                return ["SELECT c.category, COUNT(*) AS enrolled, COUNT(DISTINCT e.learner_id) AS learners FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id WHERE 1 = 1$w GROUP BY c.category ORDER BY enrolled DESC", $p];
            }, ['enrolled']);
        $add('academy', 'academy-quiz-results', 'Quiz and exam results per course', 'Attempts, pass rate and average score for every course.', $A,
            ['course' => 'Course', 'attempts' => 'Attempts', 'passed' => 'Passed', 'pass_rate' => 'Pass rate %', 'avg_score' => 'Average score %'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.submitted_at');
                return ["SELECT c.title AS course, COUNT(*) AS attempts, SUM(a.passed = 1) AS passed, ROUND(100 * SUM(a.passed = 1) / COUNT(*), 1) AS pass_rate, ROUND(AVG(a.score_pct), 1) AS avg_score FROM academy_attempts a JOIN academy_courses c ON c.id = a.course_id WHERE a.submitted_at IS NOT NULL$w GROUP BY c.id, c.title ORDER BY attempts DESC", $p];
            }, ['attempts', 'passed']);
        $add('academy', 'academy-certificates', 'Certificates issued', 'Every certificate with its number, course and status.', $A,
            ['issued_at' => 'Issued', 'code' => 'Number', 'learner_name' => 'Learner', 'course_title' => 'Course', 'score_pct' => 'Score %', 'status' => 'Status', 'fee_status' => 'Fee'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'c.issued_at');
                return ["SELECT c.issued_at, c.code, c.learner_name, c.course_title, c.score_pct, IF(c.revoked_at IS NOT NULL, 'withdrawn', IF(c.approved_at IS NULL, 'waiting for approval', 'valid')) AS status, c.fee_status FROM academy_certificates c WHERE 1 = 1$w ORDER BY c.issued_at DESC", $p];
            });
        $add('academy', 'academy-certificates-by-course', 'Certificates per course', 'How many certificates each course produced.', $A,
            ['course_title' => 'Course', 'certificates' => 'Certificates', 'avg_score' => 'Average score %'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'c.issued_at');
                return ["SELECT c.course_title, COUNT(*) AS certificates, ROUND(AVG(c.score_pct), 1) AS avg_score FROM academy_certificates c WHERE c.revoked_at IS NULL$w GROUP BY c.course_title ORDER BY certificates DESC", $p];
            }, ['certificates']);
        $add('academy', 'academy-lesson-progress', 'Lesson progress per learner', 'How many lessons each learner has completed.', $A,
            ['name' => 'Learner', 'email' => 'E-mail', 'courses' => 'Courses', 'lessons_done' => 'Lessons completed', 'last_lesson' => 'Last lesson done'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'g.completed_at');
                return ["SELECT l.name, l.email, COUNT(DISTINCT g.course_id) AS courses, COUNT(*) AS lessons_done, MAX(g.completed_at) AS last_lesson FROM academy_progress g JOIN academy_learners l ON l.id = g.learner_id WHERE 1 = 1$w GROUP BY l.id, l.name, l.email ORDER BY lessons_done DESC", $p];
            }, ['courses', 'lessons_done']);
        $add('academy', 'academy-courses', 'Course catalogue', 'Every course with its level, status and size.', $A,
            ['title' => 'Course', 'category' => 'Category', 'level' => 'Level', 'status' => 'Status', 'modules' => 'Modules', 'lessons' => 'Lessons', 'quizzes' => 'Quizzes', 'enrolled' => 'Enrolled'],
            static fn(array $r): array => ["SELECT c.title, c.category, c.level, c.status, (SELECT COUNT(*) FROM academy_modules m WHERE m.course_id = c.id) AS modules, (SELECT COUNT(*) FROM academy_lessons x WHERE x.course_id = c.id) AS lessons, (SELECT COUNT(*) FROM academy_quizzes q WHERE q.course_id = c.id) AS quizzes, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = c.id) AS enrolled FROM academy_courses c ORDER BY c.sort_order, c.title", []],
            ['modules', 'lessons', 'quizzes', 'enrolled'], false);
        $add('academy', 'academy-classes', 'Virtual classes', 'Scheduled classes with their course and platform.', $A,
            ['start_at' => 'Starts', 'title' => 'Class', 'course' => 'Course', 'platform' => 'Platform', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'k.start_at');
                return ["SELECT k.start_at, k.title, COALESCE(c.title, '(any)') AS course, k.platform, k.status FROM academy_classes k LEFT JOIN academy_courses c ON c.id = k.course_id WHERE 1 = 1$w ORDER BY k.start_at DESC", $p];
            });
        $add('academy', 'academy-facilitator-applications', 'Facilitator applications', 'People who applied to teach in the Academy.', $A,
            ['created_at' => 'Date', 'name' => 'Name', 'email' => 'E-mail', 'status' => 'Status'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT a.created_at, a.name, a.email, a.status FROM academy_facilitator_applications a WHERE 1 = 1$w ORDER BY a.created_at DESC", $p];
            });
        $add('academy', 'academy-messages', 'Learner conversations', 'Messages between the team and learners, per learner.', $A,
            ['name' => 'Learner', 'email' => 'E-mail', 'messages' => 'Messages', 'from_learner' => 'From the learner', 'unread' => 'Unread by the team', 'last_message' => 'Latest'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.created_at');
                return ["SELECT l.name, l.email, COUNT(*) AS messages, SUM(m.sender = 'learner') AS from_learner, SUM(m.sender = 'learner' AND m.read_at IS NULL) AS unread, MAX(m.created_at) AS last_message FROM academy_messages m JOIN academy_learners l ON l.id = m.learner_id WHERE 1 = 1$w GROUP BY l.id, l.name, l.email ORDER BY last_message DESC", $p];
            }, ['messages', 'from_learner', 'unread']);

        // ============================================================================================================== NEWSLETTER AND SOCIAL
        $A = 'newsletter.manage';
        $add('newsletter', 'newsletter-issues', 'Newsletter issues', 'Every issue with its e-mail delivery numbers and PDF downloads.', $A,
            ['issue_date' => 'Issue date', 'title' => 'Title', 'status' => 'Status', 'email_total' => 'Recipients', 'email_sent' => 'Sent', 'email_failed' => 'Failed', 'pdf_downloads' => 'PDF downloads'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'n.created_at');
                return ["SELECT n.issue_date, n.title, n.status, n.email_total, n.email_sent, n.email_failed, n.pdf_downloads FROM newsletters n WHERE 1 = 1$w ORDER BY n.issue_date DESC", $p];
            }, ['email_total', 'email_sent', 'email_failed', 'pdf_downloads']);
        $add('newsletter', 'newsletter-failures', 'Newsletter e-mails that failed', 'Addresses an issue could not be delivered to, with the reason.', 'newsletter.send',
            ['updated_at' => 'When', 'newsletter' => 'Issue', 'email' => 'E-mail', 'attempts' => 'Attempts', 'last_error' => 'Reason'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.updated_at');
                return ["SELECT d.updated_at, COALESCE(n.title, '(issue removed)') AS newsletter, d.email, d.attempts, d.last_error FROM newsletter_deliveries d LEFT JOIN newsletters n ON n.id = d.newsletter_id WHERE d.status = 'failed'$w ORDER BY d.updated_at DESC", $p];
            }, ['attempts']);
        $add('newsletter', 'newsletter-delivery-summary', 'Delivery summary per issue', 'Sent, pending and failed deliveries for each issue.', 'newsletter.send',
            ['newsletter' => 'Issue', 'status' => 'Delivery status', 'deliveries' => 'Deliveries'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'd.created_at');
                return ["SELECT COALESCE(n.title, '(issue removed)') AS newsletter, d.status, COUNT(*) AS deliveries FROM newsletter_deliveries d LEFT JOIN newsletters n ON n.id = d.newsletter_id WHERE 1 = 1$w GROUP BY d.newsletter_id, newsletter, d.status ORDER BY newsletter, d.status", $p];
            }, ['deliveries']);
        $add('newsletter', 'social-posts', 'Social media posts', 'Automatic shares of newsletters on social media and whether they went out.', 'social.share',
            ['created_at' => 'Date', 'channel' => 'Channel', 'newsletter' => 'Newsletter', 'status' => 'Status', 'attempts' => 'Attempts', 'posted_at' => 'Posted', 'last_error' => 'Problem'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 's.created_at');
                return ["SELECT s.created_at, s.channel, COALESCE(n.title, '') AS newsletter, s.status, s.attempts, s.posted_at, s.last_error FROM social_posts s LEFT JOIN newsletters n ON n.id = s.newsletter_id WHERE 1 = 1$w ORDER BY s.created_at DESC", $p];
            }, ['attempts']);

        // ============================================================================================================== TRAFFIC
        $A = 'settings.manage';
        $add('traffic', 'traffic-daily', 'Visits per day', 'Page views and different visitors each day.', $A,
            ['day' => 'Day', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT DATE(v.visited_at) AS day, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE 1 = 1$w GROUP BY day ORDER BY day DESC", $p];
            }, ['views']);
        $add('traffic', 'traffic-pages', 'Most visited pages', 'Which pages visitors open most.', $A,
            ['path' => 'Page', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT v.path, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE 1 = 1$w GROUP BY v.path ORDER BY views DESC", $p];
            }, ['views']);
        $add('traffic', 'traffic-countries', 'Visitors by country', 'Where visitors are.', $A,
            ['country' => 'Country', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT COALESCE(NULLIF(v.country, ''), '(unknown)') AS country, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE 1 = 1$w GROUP BY country ORDER BY views DESC", $p];
            }, ['views']);
        $add('traffic', 'traffic-devices', 'Visitors by device', 'Phone, tablet or computer.', $A,
            ['device' => 'Device', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT COALESCE(NULLIF(v.device, ''), '(unknown)') AS device, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE 1 = 1$w GROUP BY device ORDER BY views DESC", $p];
            }, ['views']);
        $add('traffic', 'traffic-referrers', 'Where visitors come from', 'The websites that send visitors to ours.', $A,
            ['referrer' => 'Website', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT COALESCE(NULLIF(v.referrer_host, ''), '(direct or unknown)') AS referrer, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE 1 = 1$w GROUP BY referrer ORDER BY views DESC", $p];
            }, ['views']);
        $add('traffic', 'traffic-hours', 'Busiest hours of the day', 'At what hour visitors arrive (server time).', $A,
            ['hour' => 'Hour', 'views' => 'Page views'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT HOUR(v.visited_at) AS hour, COUNT(*) AS views FROM site_visits v WHERE 1 = 1$w GROUP BY hour ORDER BY hour", $p];
            }, ['views']);
        $add('traffic', 'traffic-academy-pages', 'Academy page views', 'Visits to the Academy pages.', $A,
            ['path' => 'Page', 'views' => 'Page views', 'visitors' => 'Different visitors'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'v.visited_at');
                return ["SELECT v.path, COUNT(*) AS views, COUNT(DISTINCT v.visitor_code) AS visitors FROM site_visits v WHERE v.path LIKE '%academy%'$w GROUP BY v.path ORDER BY views DESC", $p];
            }, ['views']);

        // ============================================================================================================== CONTENT
        $A = 'content.manage';
        $add('content', 'content-posts', 'Blog and news posts', 'Posts with their type, status and publish date.', $A,
            ['created_at' => 'Created', 'post_type' => 'Type', 'title' => 'Title', 'category' => 'Category', 'status' => 'Status', 'published_at' => 'Published', 'author_name' => 'Author'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'p.created_at');
                return ["SELECT p.created_at, p.post_type, p.title, p.category, p.status, p.published_at, p.author_name FROM posts p WHERE 1 = 1$w ORDER BY p.created_at DESC", $p];
            });
        $add('content', 'content-posts-summary', 'Posts by type and status', 'How many posts of each kind are published or in draft.', $A,
            ['post_type' => 'Type', 'status' => 'Status', 'posts' => 'Posts'],
            static fn(array $r): array => ["SELECT p.post_type, p.status, COUNT(*) AS posts FROM posts p GROUP BY p.post_type, p.status ORDER BY p.post_type, p.status", []],
            ['posts'], false);
        $add('content', 'content-projects', 'Projects', 'Every project with its status, place and dates.', $A,
            ['title' => 'Project', 'program' => 'Programme', 'status' => 'Status', 'location' => 'Location', 'start_date' => 'Start', 'end_date' => 'End', 'active' => 'Shown on website'],
            static fn(array $r): array => ["SELECT p.title, COALESCE(g.title, '') AS program, p.status, p.location, p.start_date, p.end_date, IF(p.is_active = 1, 'yes', 'no') AS active FROM projects p LEFT JOIN programs g ON g.id = p.program_id ORDER BY p.sort_order, p.title", []],
            [], false);
        $add('content', 'content-projects-by-status', 'Projects by status', 'Planned, ongoing and completed projects.', $A,
            ['status' => 'Status', 'projects' => 'Projects'],
            static fn(array $r): array => ["SELECT p.status, COUNT(*) AS projects FROM projects p GROUP BY p.status ORDER BY projects DESC", []],
            ['projects'], false);
        $add('content', 'content-opportunities', 'Opportunities', 'Opportunities listed, by category and source.', $A,
            ['category' => 'Category', 'source' => 'Source', 'opportunities' => 'Opportunities', 'needs_review' => 'Need review'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'o.created_at');
                return ["SELECT COALESCE(NULLIF(o.category, ''), '(none)') AS category, o.source, COUNT(*) AS opportunities, SUM(o.needs_review = 1) AS needs_review FROM opportunities o WHERE 1 = 1$w GROUP BY category, o.source ORDER BY opportunities DESC", $p];
            }, ['opportunities', 'needs_review']);
        $add('content', 'content-opportunities-closing', 'Opportunities closing soon', 'Deadlines in the next 30 days.', $A,
            ['deadline' => 'Deadline', 'title' => 'Opportunity', 'organisation' => 'Organisation', 'category' => 'Category'],
            static fn(array $r): array => ["SELECT o.deadline, o.title, o.organisation, o.category FROM opportunities o WHERE o.is_active = 1 AND o.deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY o.deadline", []],
            [], false);
        $add('content', 'content-documents', 'Document downloads', 'Public documents and how often each was downloaded.', $A,
            ['title' => 'Document', 'category' => 'Category', 'year' => 'Year', 'version' => 'Version', 'download_count' => 'Downloads', 'is_public' => 'Public'],
            static fn(array $r): array => ["SELECT d.title, d.category, d.year, d.version, d.download_count, IF(d.is_public = 1, 'yes', 'no') AS is_public FROM documents d WHERE d.is_active = 1 ORDER BY d.download_count DESC", []],
            ['download_count'], false);
        $add('content', 'content-jobs', 'Jobs', 'Jobs with their closing date and number of applications.', $A,
            ['title' => 'Job', 'department' => 'Department', 'location' => 'Location', 'closing_date' => 'Closes', 'active' => 'Open', 'applications' => 'Applications'],
            static fn(array $r): array => ["SELECT j.title, j.department, j.location, j.closing_date, IF(j.is_active = 1, 'yes', 'no') AS active, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applications FROM jobs j ORDER BY j.closing_date DESC", []],
            ['applications'], false);
        $add('content', 'content-partners', 'Partners and team', 'Partners by type and team members by group.', $A,
            ['kind' => 'List', 'grp' => 'Type / group', 'people' => 'Entries'],
            static fn(array $r): array => ["SELECT 'Partners' AS kind, COALESCE(NULLIF(p.partner_type, ''), '(none)') AS grp, COUNT(*) AS people FROM partners p WHERE p.is_active = 1 GROUP BY grp UNION ALL SELECT 'Team', COALESCE(NULLIF(t.team_group, ''), '(none)'), COUNT(*) FROM team_members t WHERE t.is_active = 1 GROUP BY 2", []],
            ['people'], false);
        $add('content', 'content-pages', 'Pages', 'Every page of the website with its last change.', 'pages.edit',
            ['title' => 'Page', 'slug' => 'Address', 'updated_at' => 'Last changed'],
            static fn(array $r): array => ["SELECT p.title, p.slug, p.updated_at FROM pages p ORDER BY p.updated_at DESC", []],
            [], false);

        // ============================================================================================================== EVENTS, CALENDAR, MEETINGS
        $add('meetings', 'events-list', 'Events', 'Events with dates, capacity and registrations.', 'content.manage',
            ['start_at' => 'Starts', 'title' => 'Event', 'location' => 'Location', 'capacity' => 'Capacity', 'registrations' => 'Registrations', 'active' => 'Shown'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'e.start_at');
                return ["SELECT e.start_at, e.title, e.location, e.capacity, (SELECT COUNT(*) FROM event_registrations g WHERE g.event_id = e.id) AS registrations, IF(e.is_active = 1, 'yes', 'no') AS active FROM events e WHERE 1 = 1$w ORDER BY e.start_at DESC", $p];
            }, ['registrations']);
        $add('meetings', 'meetings-list', 'Meetings', 'Meetings with platform, status and how many people were invited.', 'meetings.manage',
            ['start_at' => 'Starts', 'title' => 'Meeting', 'platform' => 'Platform', 'status' => 'Status', 'organiser_name' => 'Organiser', 'invited' => 'Invited', 'accepted' => 'Accepted', 'declined' => 'Declined'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'm.start_at');
                return ["SELECT m.start_at, m.title, m.platform, m.status, m.organiser_name, (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL) AS invited, (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.rsvp = 'yes') AS accepted, (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.rsvp = 'no') AS declined FROM meetings m WHERE 1 = 1$w ORDER BY m.start_at DESC", $p];
            }, ['invited', 'accepted', 'declined']);
        $add('meetings', 'meetings-requests', 'Meeting requests', 'Requests people sent to meet the team.', 'meetings.manage',
            ['month' => 'Month', 'status' => 'Status', 'requests' => 'Requests'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'q.created_at');
                return ["SELECT DATE_FORMAT(q.created_at, '%Y-%m') AS month, q.status, COUNT(*) AS requests FROM meeting_requests q WHERE 1 = 1$w GROUP BY month, q.status ORDER BY month DESC", $p];
            }, ['requests']);
        $add('meetings', 'calendar-entries', 'Calendar entries by type', 'What is on the calendar, by type.', 'calendar.manage',
            ['type' => 'Type', 'entries' => 'Entries', 'public' => 'Public'],
            static fn(array $r): array => ["SELECT COALESCE(t.name, '(none)') AS type, COUNT(*) AS entries, SUM(c.is_public = 1) AS public FROM calendar_entries c LEFT JOIN calendar_types t ON t.id = c.type_id WHERE c.is_active = 1 GROUP BY c.type_id, type ORDER BY entries DESC", []],
            ['entries', 'public'], false);

        // ============================================================================================================== SECURITY AND ADMINISTRATION
        $add('security', 'audit-by-user', 'Activity by administrator', 'What each administrator did in the period.', 'audit.view',
            ['user_name' => 'Administrator', 'actions' => 'Actions', 'first' => 'First', 'last' => 'Latest'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT COALESCE(NULLIF(a.user_name, ''), '(system)') AS user_name, COUNT(*) AS actions, MIN(a.created_at) AS first, MAX(a.created_at) AS last FROM audit_log a WHERE 1 = 1$w GROUP BY user_name ORDER BY actions DESC", $p];
            }, ['actions']);
        $add('security', 'audit-by-action', 'Activity by type of action', 'The most common actions in the activity log.', 'audit.view',
            ['action' => 'Action', 'actions' => 'Times'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT a.action, COUNT(*) AS actions FROM audit_log a WHERE 1 = 1$w GROUP BY a.action ORDER BY actions DESC", $p];
            }, ['actions']);
        $add('security', 'audit-log', 'Activity log', 'Every recorded change: who, what, when.', 'audit.view',
            ['created_at' => 'When', 'user_name' => 'Who', 'action' => 'Action', 'entity_type' => 'On', 'summary' => 'What'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'a.created_at');
                return ["SELECT a.created_at, a.user_name, a.action, a.entity_type, a.summary FROM audit_log a WHERE 1 = 1$w ORDER BY a.id DESC", $p];
            });
        $add('security', 'logins-by-day', 'Sign-in attempts by day', 'Successful and failed administrator sign-ins each day.', 'audit.view',
            ['day' => 'Day', 'successful' => 'Successful', 'failed' => 'Failed'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT DATE(l.created_at) AS day, SUM(l.success = 1) AS successful, SUM(l.success = 0) AS failed FROM login_attempts l WHERE 1 = 1$w GROUP BY day ORDER BY day DESC", $p];
            }, ['successful', 'failed']);
        $add('security', 'logins-failed-ips', 'Failed sign-ins by address', 'Addresses with the most failed sign-ins: possible guessing attempts.', 'audit.view',
            ['ip' => 'Address', 'failed' => 'Failed attempts', 'accounts' => 'Different accounts tried', 'latest' => 'Latest'],
            function (array $r) use ($rng): array {
                [$w, $p] = $rng($r, 'l.created_at');
                return ["SELECT l.ip, COUNT(*) AS failed, COUNT(DISTINCT l.email) AS accounts, MAX(l.created_at) AS latest FROM login_attempts l WHERE l.success = 0$w GROUP BY l.ip ORDER BY failed DESC", $p];
            }, ['failed']);
        $add('security', 'users-list', 'Administrator accounts', 'Everyone who can sign in to the admin area, with their role and last sign-in.', 'users.manage',
            ['name' => 'Name', 'email' => 'E-mail', 'role' => 'Role', 'active' => 'Active', 'last_login_at' => 'Last sign-in', 'created_at' => 'Created'],
            static fn(array $r): array => ["SELECT u.name, u.email, u.role, IF(u.is_active = 1, 'yes', 'no') AS active, u.last_login_at, u.created_at FROM users u ORDER BY u.last_login_at DESC", []],
            [], false);

        return $all = $list;
    }

    /** @return array<string,array<string,mixed>> the reports the current user may open */
    public static function visible(): array
    {
        return array_filter(self::all(), static fn(array $r): bool => can((string) $r['ability']));
    }

    /**
     * Run a report. @param array<string,mixed> $report @param array{from:?string,to:?string} $r
     * @return array{rows:list<array<string,mixed>>,total:int,sums:array<string,float>,truncated:bool,error:?string}
     */
    public static function run(array $report, array $r, int $limit = 500): array
    {
        [$sql, $params] = ($report['sql'])($r);
        $out = ['rows' => [], 'total' => 0, 'sums' => [], 'truncated' => false, 'error' => null];
        try {
            $out['rows'] = Database::all($sql . ' LIMIT ' . max(1, $limit + 1), $params);
            $out['truncated'] = count($out['rows']) > $limit;
            $out['rows'] = array_slice($out['rows'], 0, $limit);
            $sel = ['COUNT(*) AS n'];
            foreach ($report['sum'] as $c) {
                $sel[] = 'SUM(`' . $c . '`) AS `' . $c . '`';
            }
            $t = Database::one('SELECT ' . implode(', ', $sel) . ' FROM (' . $sql . ') x', $params) ?? [];
            $out['total'] = (int) ($t['n'] ?? count($out['rows']));
            foreach ($report['sum'] as $c) {
                $out['sums'][$c] = (float) ($t[$c] ?? 0);
            }
        } catch (\Throwable $e) {
            $out['error'] = 'This report is not available yet (' . (str_contains($e->getMessage(), "doesn't exist") ? 'a table of this area has not been imported' : 'the data could not be read') . ').';
        }
        return $out;
    }
}
