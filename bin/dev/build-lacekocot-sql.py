#!/usr/bin/env python
"""Generates database/updates/2026-10-lacekocot-mentorship.sql (gallery photos, activity, news post, newsletter issue, a few cover photos).
Idempotent: gallery rows are added once per image, the post/activity/issue are upserted by slug, and a program/project photo is only set
when it still holds the starting default (an image the owner chose in the admin is never replaced)."""
import json, os
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..', 'database', 'updates', '2026-10-lacekocot-mentorship.sql')
P = 'assets/img/site/mentorship/lacekocot-%s.jpg'
ALBUM = 'Mentorship at Lacekocot Primary School'
G = [  # id, alt, caption
 ('0262', 'Mentors and visitors carrying bags across the school compound towards the classrooms', 'Arriving at Lacekocot Primary School'),
 ('0270', 'Pupils sitting on the grass in the shade of trees while mentors speak to them', 'The mentorship session under the trees'),
 ('0277', 'Mentors and guests seated on chairs on the school lawn', 'The mentors and guests who joined the session'),
 ('0281', 'Pupils in school uniform sitting on the ground and listening', 'Pupils listening during the session'),
 ('0313', 'A mentor speaking to pupils seated in front of a row of guests', 'A mentor opens the conversation'),
 ('0315', 'A speaker standing in front of pupils and seated guests in the school compound', 'Speaking to the pupils'),
 ('0327', 'A guest wearing a sash and crown addressing the gathering', 'A guest mentor shares her story'),
 ('0343', 'A man standing to speak while pupils in white shirts sit around him', 'Questions and answers with the pupils'),
 ('0353', 'Four mentors seated on chairs, one holding a phone, with packs of sanitary pads at their feet', 'Mentors ready for the discussion'),
 ('0357', 'Pupils sitting close together, some writing notes on paper', 'Pupils taking notes'),
 ('0364', 'A male mentor gesturing as he speaks to the group', 'A mentor speaking to the group'),
 ('0400', 'A young woman in an orange top talking with a pupil in school uniform', 'A mentor talks one to one with a pupil'),
 ('0407', 'A pupil standing and thinking before answering a question', 'A pupil answers a question'),
 ('0409', 'A woman in a patterned skirt speaking to rows of seated pupils', 'Speaking about growing up and health'),
 ('0431', 'A bearded mentor in a white shirt speaking to the pupils', 'A male mentor shares advice'),
 ('0454', 'A mentor reading from a sheet to pupils seated on the ground', 'Sharing information with the pupils'),
 ('0461', 'A mentor and a pupil standing facing each other during a discussion', 'A pupil joins the discussion'),
 ('0480', 'A woman in a red blouse addressing pupils while guests look on', 'Encouraging the pupils to ask questions'),
 ('0491', 'A woman in a red blouse raising a hand as she speaks', 'Making a point to the group'),
 ('0503', 'A mentor holding up a sanitary product to explain how it is used', 'A practical talk on menstrual hygiene'),
 ('0523', 'A mentor showing a reusable pad to the pupils during the demonstration', 'Showing how to use and care for pads'),
 ('0548', 'Pupils and mentors standing together and looking at the products being demonstrated', 'Pupils see the demonstration up close'),
 ('0553', 'Pupils gathered around a mentor holding a packet during the demonstration', 'Boys and girls learning together'),
 ('0568', 'Guests holding gift packs and a sash during the presentation', 'Gift packs are presented'),
 ('0570', 'A man holding a framed certificate while two women seated beside him smile', 'A certificate of appreciation is presented'),
 ('0575', 'A man and two women standing and seated under a tree during the presentation', 'Guests at the presentation'),
 ('0579', 'Packs of sanitary pads being handed out while pupils look on', 'Pads shared with the girls of the school'),
 ('0581', 'A guest handing a pack of sanitary products to a mentor', 'Sharing supplies'),
 ('0593', 'Pupils and mentors gathered around a flower bed in the school compound with spades', 'A planting moment in the school compound'),
 ('0601', 'Pupils and visitors gathering in front of the school building for a group photo', 'Gathering for the group photo'),
 ('0611', 'Pupils, teachers and mentors standing in rows in front of the school building', 'Everyone together at Lacekocot Primary School'),
 ('0615', 'Pupils and mentors cheering with raised arms in front of the school building', 'A cheer at the end of the day'),
]


def q(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


cap = dict((i, (a, c)) for i, a, c in G)


def f(i):
    return '<figure><img src="/%s" alt="%s" loading="lazy"><figcaption>%s</figcaption></figure>' % (P % i, cap[i][0], cap[i][1])


TITLE = 'Mentorship Session at Lacekocot Primary School'
SLUG = 'mentorship-session-at-lacekocot-primary-school'
EXCERPT = ('On 3 October 2026 our team and guest mentors spent the day with pupils of Lacekocot Primary School: open conversations about growing up, '
           'health and confidence, a hands-on talk on menstrual hygiene, and a moment of recognition and giving.')

body = ''.join([
    '<p class="lead">On 3 October 2026, Lumina Africa Foundation joined guest mentors at Lacekocot Primary School for a mentorship session built around one idea: '
    'young people do better when they can ask honest questions and hear honest answers from people who care.</p>',
    f('0270'),
    '<h2>A classroom without walls</h2>',
    '<p>Instead of a classroom, the session took place on the school lawn. Pupils sat in the shade of the trees in their school uniforms while the mentors and guests '
    'sat on chairs in front of them. The open setting made the conversation relaxed: pupils could see the speakers, the speakers could see every face, and nobody had to feel far from the discussion.</p>',
    f('0281'),
    '<h2>Growing up, health and confidence</h2>',
    '<p>Several mentors took turns to speak with the pupils about the changes that come with growing up, how to look after your health, and how to stay confident when you are unsure of yourself. '
    'Pupils took notes, put up their hands and, when invited, stood to answer or ask their own questions. The mentors listened as much as they spoke.</p>',
    f('0409'), f('0461'),
    '<h2>Boys and girls learning together</h2>',
    '<p>One part of the day was a practical talk on menstrual hygiene. Mentors showed the pupils how sanitary products are used and cared for, and the demonstration was held with boys and girls together, '
    'so that everyone understands what is normal, why it deserves respect and how to support a classmate or sister. Packs of sanitary pads were shared with the girls of the school.</p>',
    f('0523'), f('0553'),
    '<h2>Recognition and giving</h2>',
    '<p>The programme also made room for thanks. A certificate of appreciation was presented, gift packs were handed over, and guests joined the pupils for a planting moment in the school compound, '
    'a small, lasting reminder of the day.</p>',
    f('0570'), f('0593'),
    '<h2>Why mentorship matters to us</h2>',
    '<p>Mentorship is one of the ways Lumina Africa Foundation hopes to help young people in Northern Uganda learn, grow and thrive. Sessions like this one need only willing people and a shady tree, '
    'and they give pupils someone to look up to and somewhere to bring their questions.</p>',
    '<p>Thank you to the headteacher, the teachers and the pupils of Lacekocot Primary School for welcoming us, and to every mentor and guest who gave their time.</p>',
    f('0611'),
    '<h3>Bring a mentorship session to your school</h3>',
    '<p>If you lead a school, a youth group or a community and would like a mentorship session, or if you would like to volunteer as a mentor, '
    '<a href="/contact">contact us</a> or read how you can <a href="/involvement">get involved</a>. More photos are in our <a href="/gallery">gallery</a>.</p>',
])

ACT_SUMMARY = 'A day of open conversation with pupils on growing up, health and confidence, including a hands-on talk on menstrual hygiene.'
act_body = ('<p>On 3 October 2026 our team and guest mentors visited Lacekocot Primary School for a mentorship session held on the school lawn. '
            'Mentors spoke with the pupils about growing up, health and confidence, answered their questions, and led a practical talk on menstrual hygiene for boys and girls together. '
            'The day closed with a certificate of appreciation, gift packs and a planting moment in the compound.</p>'
            '<p><a href="/news/' + SLUG + '">Read the full story</a> or see the photos in the <a href="/gallery">gallery</a>.</p>')


def nimg(i):
    return {'path': P % i, 'alt': cap[i][0], 'caption': cap[i][1]}


NOIMG = {'path': '', 'alt': '', 'caption': ''}
blocks = [
    {'id': 'lk01', 'type': 'article', 'heading': 'A classroom without walls',
     'body_html': '<p>The session took place on the school lawn. Pupils sat in the shade in their uniforms while the mentors and guests sat in front of them, which kept the conversation relaxed and open: everyone could see the speakers, and the speakers could see every face.</p>',
     'image': nimg('0281'), 'link': {'label': '', 'url': ''}},
    {'id': 'lk02', 'type': 'article', 'heading': 'Growing up, health and confidence',
     'body_html': '<p>Mentors took turns to talk about the changes that come with growing up, looking after your health and staying confident when you are unsure of yourself. Pupils took notes, raised their hands and, when invited, stood to answer or to ask their own questions.</p>',
     'image': nimg('0409'), 'link': {'label': '', 'url': ''}},
    {'id': 'lk03', 'type': 'image', 'image': nimg('0461')},
    {'id': 'lk04', 'type': 'article', 'heading': 'Boys and girls learning together',
     'body_html': '<p>A practical talk on menstrual hygiene was held with boys and girls together, so that everyone understands what is normal, why it deserves respect and how to support a classmate or sister. Packs of sanitary pads were shared with the girls of the school.</p>',
     'image': nimg('0523'), 'link': {'label': '', 'url': ''}},
    {'id': 'lk05', 'type': 'image', 'image': nimg('0553')},
    {'id': 'lk06', 'type': 'divider'},
    {'id': 'lk07', 'type': 'article', 'heading': 'Recognition and giving',
     'body_html': '<p>A certificate of appreciation was presented, gift packs were handed over, and guests joined the pupils for a planting moment in the school compound, a small, lasting reminder of the day.</p>',
     'image': nimg('0570'), 'link': {'label': 'Read the full story', 'url': '/news/' + SLUG}},
    {'id': 'lk08', 'type': 'image', 'image': nimg('0611')},
    {'id': 'lk09', 'type': 'article', 'heading': 'Bring a mentorship session to your school',
     'body_html': '<p>If you lead a school, a youth group or a community and would like a mentorship session, or you would like to volunteer as a mentor, we would love to hear from you.</p>',
     'image': NOIMG, 'link': {'label': 'Contact us', 'url': '/contact'}},
    {'id': 'lk10', 'type': 'button', 'button': {'label': 'Get involved', 'url': '/involvement', 'style': 'primary'}},
]
content = {'version': 1, 'featured': {
    'heading': 'Mentorship at Lacekocot Primary School',
    'body_html': '<p>On 3 October 2026 our team and guest mentors spent the day with the pupils of Lacekocot Primary School: honest conversations about growing up, health and confidence, a hands-on talk on menstrual hygiene, and a moment of recognition and giving.</p><p>This issue shares the day in pictures and words.</p>',
    'image': nimg('0270'), 'link': {'label': 'Read the full story', 'url': '/news/' + SLUG}}, 'blocks': blocks}
NL_SLUG = 'mentorship-at-lacekocot-primary-school-october-2026'
NL_TITLE = 'Mentorship at Lacekocot Primary School'
NL_SUM = 'A day of mentorship, health talks and community spirit with the pupils of Lacekocot Primary School, 3 October 2026.'

out = ['-- Lacekocot Primary School mentorship (3 October 2026): gallery photos, activity, news post, newsletter issue and cover photos.',
       '-- Generated by bin/dev/build-lacekocot-sql.py. Safe to run more than once. The photos themselves are in assets/img/site/mentorship/.',
       'SET NAMES utf8mb4;']
for n, (i, alt, c) in enumerate(G):
    out.append("INSERT INTO gallery_images (image, alt_text, caption, album, is_active, sort_order) SELECT %s, %s, %s, %s, 1, %d FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM gallery_images WHERE image = %s);"
               % (q(P % i), q(alt), q(c), q(ALBUM), -100 + n, q(P % i)))
out.append("INSERT INTO activities (slug, title, summary, body, image, image_alt, activity_date, location, is_active, sort_order) VALUES (%s, %s, %s, %s, %s, %s, '2026-10-03', %s, 1, 0) "
           "ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), body = VALUES(body), image = VALUES(image), image_alt = VALUES(image_alt), activity_date = VALUES(activity_date), location = VALUES(location);"
           % (q(SLUG), q(TITLE), q(ACT_SUMMARY), q(act_body), q(P % '0270'), q(cap['0270'][0]), q('Lacekocot Primary School')))
out.append("INSERT INTO posts (post_type, slug, title, excerpt, body, cover_image, cover_alt, category, status, published_at, author_name, is_featured, seo_title, seo_description) VALUES ('news', %s, %s, %s, %s, %s, %s, 'Mentorship', 'published', '2026-10-23 09:00:00', 'Lumina Africa Foundation', 1, %s, %s) "
           "ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), body = VALUES(body), cover_image = VALUES(cover_image), cover_alt = VALUES(cover_alt), category = VALUES(category), seo_title = VALUES(seo_title), seo_description = VALUES(seo_description);"
           % (q(SLUG), q(TITLE), q(EXCERPT), q(body), q(P % '0270'), q(cap['0270'][0]), q(TITLE + ' | Lumina Africa Foundation'), q(EXCERPT[:290])))
out.append("INSERT INTO newsletters (slug, kind, title, summary, issue_date, cover_image, cover_alt, content, status, published_at, version) VALUES (%s, 'online', %s, %s, '2026-10-23', %s, %s, %s, 'published', '2026-10-23 09:00:00', 1) "
           "ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), cover_image = VALUES(cover_image), cover_alt = VALUES(cover_alt);"
           % (q(NL_SLUG), q(NL_TITLE), q(NL_SUM), q(P % '0270'), q(cap['0270'][0]), q(json.dumps(content, ensure_ascii=False, separators=(',', ':')))))


def setimg(table, where, i):
    out.append("UPDATE %s SET image = %s%s WHERE %s;" % (table, q(P % i), (', image_alt = ' + q(cap[i][0])) if table == 'programs' else '', where))


# a program / project cover only while it still holds the starting default (or none)
setimg('programs', "slug = 'health-community-wellbeing' AND image = 'assets/img/site/photo-04.jpg'", '0553')
setimg('programs', "slug = 'education-skills-youth-development' AND image = 'assets/img/site/photo-06.jpg'", '0409')
for slug, i in [('youth-mentorship', '0315'), ('girls-education-young-womens-empowerment', '0327'), ('community-health-education', '0523'),
                ('youth-health-wellbeing', '0548'), ('maternal-family-health-awareness', '0579'),
                ('tree-planting-ecosystem-restoration-and-biodiversity-conservation', '0593'),
                ('leadership-personal-development', '0343'), ('community-health-outreach', '0611')]:
    out.append("UPDATE projects SET image = %s WHERE slug = %s AND (image IS NULL OR image = '');" % (q(P % i), q(slug)))
os.makedirs(os.path.dirname(OUT), exist_ok=True)
open(OUT, 'w', encoding='utf-8', newline='\n').write('\n'.join(out) + '\n')
print('wrote', os.path.normpath(OUT))
