<?php
/**
 * MEETING E-MAIL (HTML) for the invitation, update, cancellation, removal, reminders, organiser message and the meeting-request e-mails.
 * Rendered ONLY by App\Services\Meetings\MeetingRenderer (the single source of the wording, so the e-mail and the invitee page cannot drift).
 * Mail-client rules: table layout, inline CSS only, web-safe fonts (Georgia for headings), absolute URLs, no scripts, hidden preheader,
 * every text cell wraps long words and URLs. There is NO unsubscribe link: these are transactional messages to one person.
 *
 * Vars: $subject, $preheader, $heading, $kicker, $introHtml (escaped paragraphs), $rows (list of [label, text, url|null]), $join (null|[label,url]),
 * $secondary (null|[label,url]), $agendaLabel, $agendaHtml, $bodyHtml (organiser message), $rsvp (null|[title, buttons:[[label,url,tone]]]),
 * $calendar (null|[label,url]), $notes (list<string>), $footerLines (list<string>), $notice (string, test notice), $logoUrl, $orgName, $cancelled (bool).
 * Brand colours: navy #2C3950, gold #D6A151, cta #B85A14, ink #1D1E20, soft ink #5A6470, cream #F7F3EC.
 */
$serif = "Georgia,'Times New Roman',Times,serif";
$sans = "'DM Sans',Arial,Helvetica,sans-serif";
$wrap = 'word-break:break-word;overflow-wrap:anywhere;word-wrap:break-word;';
$tone = ['yes' => '#2C3950', 'maybe' => '#8A5A06', 'no' => '#7a1f12'];
?>
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light">
<title><?= e($subject) ?></title>
</head>
<body style="margin:0;padding:0;background:#F7F3EC;">
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;"><?= e($preheader) ?>&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F7F3EC;">
<tr><td align="center" style="padding:24px 12px;">
    <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;table-layout:fixed;">
        <?php if ($notice !== ''): ?>
        <tr><td style="padding:0 0 12px 0;font-family:<?= $sans ?>;font-size:13px;line-height:1.5;color:#7a1f12;text-align:center;<?= $wrap ?>"><strong><?= e($notice) ?></strong></td></tr>
        <?php endif; ?>
        <tr><td style="background:#ffffff;border-radius:16px;overflow:hidden;<?= $wrap ?>">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed;">
                <tr><td style="background:#2C3950;padding:20px 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                        <?php if ($logoUrl !== ''): ?><td style="padding:0 12px 0 0;vertical-align:middle;"><img src="<?= e($logoUrl) ?>" width="40" height="40" alt="" style="display:block;border:0;width:40px;height:40px;"></td><?php endif; ?>
                        <td style="vertical-align:middle;font-family:<?= $serif ?>;font-size:20px;line-height:1.3;color:#ffffff;<?= $wrap ?>"><?= e($orgName) ?></td>
                    </tr></table>
                </td></tr>
                <tr><td style="padding:32px 32px 8px 32px;font-family:<?= $sans ?>;color:#1D1E20;<?= $wrap ?>">
                    <?php if ($kicker !== ''): ?><p style="margin:0 0 8px 0;font-size:12px;line-height:1.4;letter-spacing:1.5px;text-transform:uppercase;color:#8A5A06;font-weight:bold;"><?= e($kicker) ?></p><?php endif; ?>
                    <h1 style="margin:0 0 12px 0;font-family:<?= $serif ?>;font-size:28px;line-height:1.25;color:#2C3950;font-weight:bold;<?= $wrap ?>"><?= e($heading) ?></h1>
                    <?php if ($introHtml !== ''): ?><div style="font-size:16px;line-height:1.6;color:#1D1E20;"><?= $introHtml ?></div><?php endif; ?>
                </td></tr>
                <?php if ($rows !== []): ?>
                <tr><td style="padding:8px 32px 8px 32px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed;background:#F7F3EC;border-radius:12px;">
                        <?php foreach ($rows as $i => [$label, $text, $url]): ?>
                        <tr><td style="padding:<?= $i === 0 ? '16px' : '4px' ?> 20px <?= $i === count($rows) - 1 ? '16px' : '4px' ?> 20px;font-family:<?= $sans ?>;font-size:15px;line-height:1.55;color:#1D1E20;<?= $wrap ?>">
                            <span style="display:block;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#5A6470;font-weight:bold;"><?= e($label) ?></span>
                            <?php if ($url): ?><a href="<?= e($url) ?>" style="color:#8A4208;text-decoration:underline;<?= $wrap ?>"><?= e($text) ?></a><?php else: ?><?= nl2br(e($text)) ?><?php endif; ?>
                        </td></tr>
                        <?php endforeach; ?>
                    </table>
                </td></tr>
                <?php endif; ?>
                <?php if ($join): ?>
                <tr><td align="center" style="padding:20px 32px 4px 32px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#B85A14" style="border-radius:999px;background:#B85A14;">
                        <a href="<?= e($join[1]) ?>" style="display:inline-block;padding:16px 40px;font-family:<?= $sans ?>;font-size:17px;line-height:1.2;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:999px;"><?= e($join[0]) ?></a>
                    </td></tr></table>
                </td></tr>
                <?php endif; ?>
                <?php if ($secondary): ?>
                <tr><td align="center" style="padding:12px 32px 4px 32px;font-family:<?= $sans ?>;font-size:15px;line-height:1.5;"><a href="<?= e($secondary[1]) ?>" style="color:#8A4208;text-decoration:underline;"><?= e($secondary[0]) ?></a></td></tr>
                <?php endif; ?>
                <?php if ($bodyHtml !== ''): ?>
                <tr><td style="padding:16px 32px 0 32px;font-family:<?= $sans ?>;font-size:16px;line-height:1.6;color:#1D1E20;<?= $wrap ?>"><?= $bodyHtml ?></td></tr>
                <?php endif; ?>
                <?php if ($agendaHtml !== ''): ?>
                <tr><td style="padding:20px 32px 0 32px;font-family:<?= $sans ?>;font-size:15px;line-height:1.6;color:#1D1E20;<?= $wrap ?>">
                    <p style="margin:0 0 8px 0;font-family:<?= $serif ?>;font-size:19px;line-height:1.3;color:#2C3950;font-weight:bold;"><?= e($agendaLabel) ?></p>
                    <?= $agendaHtml ?>
                </td></tr>
                <?php endif; ?>
                <?php if ($rsvp): ?>
                <?php /* The answer buttons: inline-block links that WRAP onto a second line on a narrow phone. They must never sit in a shrinking table row or in a cell
                         that may break words ($wrap), or "Accept" is split in the middle of the word. Each is at least 44 px high. */ ?>
                <tr><td style="padding:12px 32px 0 32px;font-family:<?= $sans ?>;">
                    <p style="margin:0 0 10px 0;font-family:<?= $serif ?>;font-size:19px;line-height:1.3;color:#2C3950;font-weight:bold;<?= $wrap ?>"><?= e($rsvp['title']) ?></p>
                    <div style="margin:0;padding:0;font-size:15px;line-height:20px;">
                        <?php foreach ($rsvp['buttons'] as [$label, $url, $t]): ?><a href="<?= e($url) ?>" style="display:inline-block;margin:0 8px 8px 0;padding:12px 22px;font-family:<?= $sans ?>;font-size:15px;line-height:20px;font-weight:bold;white-space:nowrap;word-break:normal;overflow-wrap:normal;color:<?= $tone[$t] ?? '#2C3950' ?>;text-decoration:none;border:2px solid <?= $tone[$t] ?? '#2C3950' ?>;border-radius:999px;"><?= e($label) ?></a> <?php endforeach; ?>
                    </div>
                </td></tr>
                <?php endif; ?>
                <?php if ($calendar): ?>
                <tr><td style="padding:4px 32px 0 32px;font-family:<?= $sans ?>;font-size:15px;line-height:1.5;"><a href="<?= e($calendar[1]) ?>" style="color:#8A4208;text-decoration:underline;"><?= e($calendar[0]) ?></a></td></tr>
                <?php endif; ?>
                <tr><td style="padding:20px 32px 32px 32px;font-family:<?= $sans ?>;font-size:13px;line-height:1.6;color:#5A6470;<?= $wrap ?>">
                    <?php foreach ($notes as $n): ?><p style="margin:0 0 8px 0;"><?= e($n) ?></p><?php endforeach; ?>
                </td></tr>
            </table>
        </td></tr>
        <tr><td align="center" style="padding:20px 24px 8px 24px;font-family:<?= $sans ?>;font-size:12px;line-height:1.6;color:#5A6470;<?= $wrap ?>">
            <?php foreach ($footerLines as $line): ?><p style="margin:0 0 10px 0;"><?= nl2br(e($line)) ?></p><?php endforeach; ?>
        </td></tr>
    </table>
    <!--[if mso]></td></tr></table><![endif]-->
</td></tr>
</table>
</body>
</html>
