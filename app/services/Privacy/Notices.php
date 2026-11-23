<?php
declare(strict_types=1);

namespace App\Services\Privacy;

/**
 * The short "Why we ask for this" note shown next to every form that collects personal data, with a link to the matching row of the privacy policy.
 * Wording is editable in Admin > Pages > Privacy Policy > "Notes next to forms" (content('privacy', 'notes.<key>')); the defaults live here.
 * Use in a view:  <?= partial('partials/privacy-note', ['key' => 'contact']) ?>
 * Each key's anchor (#collect-<key>) is a row id in the privacy policy table, so the link opens the right row.
 */
final class Notices
{
    /** key => [label, default text]. Texts are plain, friendly, and say what the data is for. */
    public const ITEMS = [
        'contact'    => ['Contact form and meeting requests', 'We use your name, email and message only to reply to you and to keep a record of the conversation. We do not add you to any mailing list from this form.'],
        'newsletter' => ['Newsletter', 'We use your email address only to send you our newsletter. You can unsubscribe with one click in any e-mail. Subscribing is your choice and means you agree to this.'],
        'donate'     => ['Donations and pledges', 'We use these details to record and receipt your gift, to thank you, and to keep our accounts. Card and mobile-money payments are handled by the payment provider on its own page; we never see your card number.'],
        'shop'       => ['Shop orders', 'We use these details to prepare and deliver your order, to confirm your payment and to keep our accounts. Payment providers receive only what they need to take your payment.'],
        'events'     => ['Event registration', 'We use your details to register you for the event, to send you event information, and to count who attended. We do not use them for anything else without asking you.'],
        'careers'    => ['Jobs, volunteering and applications', 'We use your details, CV and photo (if you add them) only to consider your application and to contact you about it. Only the people who review applications can open them.'],
        'ambassador' => ['Ambassador applications', 'We use your details and portrait only to consider your application and, if you become an ambassador, to show your profile on this website as you agreed.'],
        'involvement' => ['Getting involved', 'We use your details only to answer you about the way you want to help and to arrange it with you.'],
        'membership' => ['Membership', 'We use these details to review your application, run your membership, issue your card and receipts, and contact you. Your photo is used on your membership card only if you agree.'],
        'academy'    => ['LAF Academy learners', 'We use your details to open your learner account, run your courses and exams, record your results, issue certificates and keep in touch about your learning. Your picture is used so that your certificate and registration are really yours.'],
        'facilitator' => ['Academy facilitator applications', 'We use your details, CV and photo only to consider your application to teach with the Academy and to contact you about it.'],
        'fund'       => ['Fund a project', 'We use your name and contact details only to follow up your proposal or pledge and to keep a record of it.'],
        'forms'      => ['Other forms on this website', 'We use what you type only for the purpose this form states, and only the people who deal with this form can see it.'],
        'bono'       => ['Bono, the Academy assistant', 'Your question is sent to our AI service provider to get an answer. Please do not type personal or private details into it.'],
    ];

    public static function anchor(string $key): string
    {
        return 'collect-' . $key;
    }

    public static function text(string $key): string
    {
        $default = self::ITEMS[$key][1] ?? '';
        $t = trim((string) content('privacy', 'notes.' . $key, $default));
        return $t !== '' ? $t : $default;
    }

    public static function url(string $key): string
    {
        return url('privacy') . '#' . self::anchor($key);
    }
}
