"""Lumina Africa Foundation awareness flyer, A4 portrait. Writes a screen PDF and a print PDF (3 mm bleed + crop marks).
Run: "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" docs/brand/flyer/build_flyer.py
Brand tokens, fonts, logo and QR helper come from ../id-card/build_id_card.py.
"""
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(HERE, '..', 'id-card'))
from PIL import Image
from reportlab.pdfgen import canvas
from reportlab.lib.units import mm
from reportlab.lib.colors import HexColor, white
from reportlab.lib.utils import simpleSplit
import build_id_card as b
from build_id_card import NAVY, GOLD, GOLD_TXT, INK, SOFT, CREAM, F, FB, rect, text, qr

W, H = 210.0, 297.0
M = 14.0                       # side margin
LINE = HexColor('#E3DDD2')
PHOTO = os.path.join(HERE, '..', '..', '..', 'assets', 'img', 'site', 'photo-03.jpg')
OUT = os.path.join(HERE, 'lumina-awareness-flyer.pdf')

FOCUS = (
    ('health', 'Health & Community Wellbeing',
     'Promoting health awareness, so communities have reliable information and can make informed decisions.'),
    ('education', 'Education, Skills & Youth Development',
     'Learning, mentorship and skills for employment, entrepreneurship and active community participation.'),
    ('tech', 'Technology & Digital Inclusion',
     'Expanding access to technology and building the digital skills young people need in today’s economy.'),
    ('climate', 'Climate Resilience & Justice',
     'Supporting climate awareness and justice initiatives where young people take the lead.'),
)


def glyph(c, kind, cx, cy):
    """Simple navy glyphs, drawn inside a gold-tint circle centred on (cx, cy) in mm."""
    c.setFillColor(NAVY)
    c.setStrokeColor(NAVY)
    c.setLineWidth(0.7 * mm)
    c.setLineCap(1)
    c.setLineJoin(1)
    if kind == 'health':
        c.rect((cx - 1.2) * mm, (cy - 4.2) * mm, 2.4 * mm, 8.4 * mm, stroke=0, fill=1)
        c.rect((cx - 4.2) * mm, (cy - 1.2) * mm, 8.4 * mm, 2.4 * mm, stroke=0, fill=1)
    elif kind == 'education':   # open book
        for s in (-1, 1):
            p = c.beginPath()
            p.moveTo(cx * mm, (cy - 3) * mm)
            p.lineTo((cx + s * 5) * mm, (cy - 2) * mm)
            p.lineTo((cx + s * 5) * mm, (cy + 3.6) * mm)
            p.lineTo(cx * mm, (cy + 2.6) * mm)
            p.close()
            c.drawPath(p, stroke=1, fill=0)
    elif kind == 'tech':        # laptop
        c.roundRect((cx - 3.6) * mm, (cy - 2.2) * mm, 7.2 * mm, 5.2 * mm, 0.5 * mm, stroke=1, fill=0)
        c.line((cx - 5) * mm, (cy - 3.8) * mm, (cx + 5) * mm, (cy - 3.8) * mm)
    else:                       # leaf
        p = c.beginPath()
        p.moveTo((cx - 4) * mm, (cy - 3.6) * mm)
        p.curveTo((cx - 5) * mm, (cy + 3) * mm, (cx + 1) * mm, (cy + 5.4) * mm, (cx + 4.6) * mm, (cy + 4.4) * mm)
        p.curveTo((cx + 5.2) * mm, (cy - 1) * mm, (cx + 1.4) * mm, (cy - 4.4) * mm, (cx - 4) * mm, (cy - 3.6) * mm)
        p.close()
        c.drawPath(p, stroke=1, fill=0)
        c.line((cx - 4) * mm, (cy - 3.6) * mm, (cx + 1.6) * mm, (cy + 1.4) * mm)


def card(c, x, y, w, h, kind, title, body):
    c.setFillColor(white)
    c.setStrokeColor(LINE)
    c.setLineWidth(0.3 * mm)
    c.roundRect(x * mm, y * mm, w * mm, h * mm, 3 * mm, stroke=1, fill=1)
    rect(c, x + 6, y + h - 0.9, 14, 0.9, GOLD)
    cx, cy = x + 12.5, y + h - 12.5
    c.setFillColor(HexColor('#F6E9D2'))
    c.circle(cx * mm, cy * mm, 7 * mm, stroke=0, fill=1)
    glyph(c, kind, cx, cy)
    tx = x + 23
    ty = y + h - 9.0
    for i, ln in enumerate(simpleSplit(title, FB, 11, (w - 27) * mm)):
        text(c, tx, ty - i * 4.6, ln, FB, 11, NAVY)
    top = ty - len(simpleSplit(title, FB, 11, (w - 27) * mm)) * 4.6 - 1.2
    c.setFillColor(SOFT)
    for i, ln in enumerate(simpleSplit(body, F, 8.6, (w - 12) * mm)):
        text(c, x + 6, min(top, y + h - 21) - 2.0 - i * 4.0, ln, F, 8.6, SOFT)


def draw(c):
    B = b.BLEED
    rect(c, -B, -B, W + 2 * B, H + 2 * B, white)
    # soft cream cap behind the header
    rect(c, -B, 262, W + 2 * B, 35 + B, CREAM)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.8 * mm)
    c.line(-B * mm, 262 * mm, (W + B) * mm, 262 * mm)
    c.drawImage(b.LOGO, M * mm, 270 * mm, 17 * mm, 17 * mm, mask='auto')
    text(c, M + 21, 281.2, 'LUMINA AFRICA', FB, 15, NAVY, sp=0.4)
    text(c, M + 21, 274.4, 'FOUNDATION', FB, 9.5, GOLD_TXT, sp=2.0)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.4 * mm)
    c.roundRect((W - M - 40) * mm, 275 * mm, 40 * mm, 8 * mm, 4 * mm, stroke=1, fill=0)
    text(c, W - M - 20, 277.9, 'AWARENESS', FB, 7.5, GOLD_TXT, 'c', 1.2)
    # headline
    text(c, M, 245, 'Building Pathways', FB, 36, NAVY)
    text(c, M, 230.5, 'to Opportunity.', FB, 36, GOLD_TXT)
    for i, ln in enumerate(simpleSplit('Four focus areas, one goal: young people in Northern Uganda are not left behind in a world that is becoming digital.', F, 12, (W - 2 * M) * mm)):
        text(c, M, 220.2 - i * 5.6, ln, F, 12, SOFT)
    # photo
    px, py, pw, ph = M, 158, W - 2 * M, 51
    c.setFillColor(GOLD)
    c.roundRect((px - 0.8) * mm, (py - 0.8) * mm, (pw + 1.6) * mm, (ph + 1.6) * mm, 4.4 * mm, stroke=0, fill=1)
    c.saveState()
    p = c.beginPath()
    p.roundRect(px * mm, py * mm, pw * mm, ph * mm, 3.6 * mm)
    c.clipPath(p, stroke=0, fill=0)
    im = Image.open(PHOTO)
    s = pw / im.width
    dh = im.height * s
    off = max(0, min(dh - ph, dh * 0.36 - ph / 2))      # keep the instructor and screens in frame
    c.drawImage(PHOTO, px * mm, (py - (dh - ph) + off) * mm, pw * mm, dh * mm)
    c.restoreState()
    rect(c, px + 5, py + 5, 78, 8, white, 4)
    text(c, px + 9, py + 7.6, 'Young people. Digital skills. A stronger future.', FB, 7.6, NAVY)
    # focus areas
    text(c, M, 149.5, 'OUR FOCUS AREAS', FB, 9, GOLD_TXT, sp=1.8)
    c.setStrokeColor(LINE)
    c.setLineWidth(0.3 * mm)
    c.line((M + 47) * mm, 150.5 * mm, (W - M) * mm, 150.5 * mm)
    cw, ch, gap = (W - 2 * M - 6) / 2, 34, 6
    for i, (k, t, d) in enumerate(FOCUS):
        x = M + (i % 2) * (cw + gap)
        y = 139.5 - ch - (i // 2) * (ch + 4)
        card(c, x, y, cw, ch, k, t, d)
    # awareness band
    by = 47
    rect(c, M, by, W - 2 * M, 14, CREAM, 2)
    rect(c, M, by, 1.8, 14, GOLD)
    msg = 'More than two decades of conflict disrupted schooling and livelihoods in Northern Uganda. Digital skills help a generation catch up.'
    for i, ln in enumerate(simpleSplit(msg, FB, 9.6, (W - 2 * M - 14) * mm)):
        text(c, M + 7, by + 8.4 - i * 4.8, ln, FB, 9.6, NAVY)
    # call to action
    text(c, M, 38.2, 'Be part of the change.', FB, 17, NAVY)
    text(c, M, 32.2, 'VOLUNTEER   •   PARTNER   •   DONATE', FB, 8.6, GOLD_TXT, sp=1.2)
    for i, ln in enumerate((b.PHONE + '   |   +256 790 635 544', b.EMAIL, b.WEB, 'Gulu City, Northern Uganda')):
        text(c, M, 26.0 - i * 4.6, ln, F, 9.4, INK)
    qr(c, W - M - 30, 14, 30)
    text(c, W - M - 15, 11.2, 'SCAN TO LEARN MORE', FB, 6.6, NAVY, 'c', 0.8)
    # footer
    text(c, W / 2, 6.6, 'Programme areas shown are planned focus areas. ' + b.REG, F, 6.4, SOFT, 'c')
    rect(c, -B, -B, W + 2 * B, 3.4 + B, GOLD)


def render(path, bleed=0.0, margin=8.0):
    b.BLEED = bleed
    b.prep_logo()
    sw, sh = (W + 2 * margin, H + 2 * margin) if bleed else (W, H)
    c = canvas.Canvas(path, pagesize=(sw * mm, sh * mm))
    c.setTitle('Lumina Africa Foundation - awareness flyer')
    c.setAuthor('Lumina Africa Foundation')
    c.saveState()
    if bleed:
        c.translate(margin * mm, margin * mm)
    draw(c)
    if bleed:
        b.crop_marks(c, W, H, margin, bleed)
    c.restoreState()
    if bleed:
        c.setTrimBox((margin * mm, margin * mm, (margin + W) * mm, (margin + H) * mm))
        c.setBleedBox(((margin - bleed) * mm, (margin - bleed) * mm, (margin + W + bleed) * mm, (margin + H + bleed) * mm))
    c.showPage()
    c.save()
    b.BLEED = 0.0
    print('wrote', path)


if __name__ == '__main__':
    render(OUT)
    render(OUT.replace('.pdf', '-print.pdf'), bleed=3.0)
