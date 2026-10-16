"""Lumina Africa Foundation ID card (CR80 landscape, 85.6 x 54 mm). Front + back -> PDF.
Run: "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" docs/brand/id-card/build_id_card.py
Edit the CARD dict below for a real holder; photo.jpg next to this script (if present) replaces the placeholder.
"""
import os
from PIL import Image, ImageDraw
from reportlab.pdfgen import canvas
from reportlab.lib.units import mm
from reportlab.lib.colors import HexColor, white
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.graphics.barcode.code128 import Code128

HERE = os.path.dirname(os.path.abspath(__file__))
LOGO_SRC = os.path.join(HERE, '..', 'logo-original.png')
LOGO = os.path.join(HERE, 'logo-round.png')
LOGO_FAINT = os.path.join(HERE, 'logo-faint.png')
PHOTO = os.path.join(HERE, 'photo.jpg')
OUT = os.path.join(HERE, 'lumina-id-card.pdf')

NAVY, NAVY_D, GOLD = HexColor('#2C3950'), HexColor('#172036'), HexColor('#D6A151')
GOLD_TXT, CREAM, INK, SOFT = HexColor('#8A5A06'), HexColor('#F7F3EC'), HexColor('#1D1E20'), HexColor('#5A6470')
PALE = HexColor('#C9D1DF')

CARD = dict(name='FULL NAME', role='ROLE / POSITION', dept='Department name', prog='Programme or location',
            valid='DD MMM YYYY', issued='DD MMM YYYY', idno="LAF-ID-0001")

F, FB = 'Helvetica', 'Helvetica-Bold'
for n, f in (('Seg', 'segoeui.ttf'), ('Seg-B', 'segoeuib.ttf')):
    p = os.path.join(os.environ.get('WINDIR', r'C:\Windows'), 'Fonts', f)
    if os.path.exists(p):
        pdfmetrics.registerFont(TTFont(n, p))
        F, FB = 'Seg', 'Seg-B'

W, H = 85.6, 54.0
R = 3.2


def prep_logo():
    im = Image.open(LOGO_SRC).convert('RGB')
    c, r = im.width // 2, int(im.width * 0.452)
    im = im.crop((c - r, c - r, c + r, c + r)).resize((600, 600), Image.LANCZOS)
    m = Image.new('L', (2400, 2400), 0)
    ImageDraw.Draw(m).ellipse((0, 0, 2399, 2399), fill=255)
    m = m.resize((600, 600), Image.LANCZOS)
    out = im.convert('RGBA')
    out.putalpha(m)
    out.save(LOGO)
    f = out.copy()
    f.putalpha(m.point(lambda v: int(v * 0.07)))
    f.save(LOGO_FAINT)


BLEED = 0.0          # mm; > 0 only while rendering the print file (square, die-cut by the printer)
SITE_URL = 'https://www.luminaafricafoundation.com/'
ADDRESS = ('Bank Lane Road, Gulu City', 'Northern Uganda')
PHONE = '+256 779 712 990'
EMAIL = 'info@luminaafricafoundation.com'
WEB = 'www.luminaafricafoundation.com'
REG = 'Reg. No. LAF-001'


def clip_card(c, w=None, h=None):
    w, h = w or W, h or H
    p = c.beginPath()
    if BLEED:
        p.rect(-BLEED * mm, -BLEED * mm, (w + 2 * BLEED) * mm, (h + 2 * BLEED) * mm)
    else:
        p.roundRect(0, 0, w * mm, h * mm, R * mm)
    c.clipPath(p, stroke=0, fill=0)


def full(c, col, w=None, h=None):
    """Fill the whole card, including the bleed when printing."""
    rect(c, -BLEED, -BLEED, (w or W) + 2 * BLEED, (h or H) + 2 * BLEED, col)


def qr(c, x, y, size, data=SITE_URL):
    """Scannable QR on a white tile with a quiet zone. (x, y) = bottom-left of the tile."""
    from reportlab.graphics.barcode.qr import QrCodeWidget
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics import renderPDF
    pad = 1.3
    rect(c, x, y, size, size, white, 1.0)
    c.setStrokeColor(HexColor('#CFC7B8'))
    c.setLineWidth(0.2 * mm)
    c.roundRect(x * mm, y * mm, size * mm, size * mm, 1.0 * mm, stroke=1, fill=0)
    w = QrCodeWidget(data, barLevel='M', barBorder=0)
    w.barFillColor = INK
    x0, y0, x1, y1 = w.getBounds()
    s = (size - 2 * pad) * mm / (x1 - x0)
    d = Drawing((size - 2 * pad) * mm, (size - 2 * pad) * mm, transform=[s, 0, 0, s, -x0 * s, -y0 * s])
    d.add(w)
    renderPDF.draw(d, c, (x + pad) * mm, (y + pad) * mm)


def crop_marks(c, w, h, m, bleed):
    """Trim marks at the 4 corners; the sheet origin is the card's bottom-left trim corner."""
    c.setStrokeColor(HexColor('#000000'))
    c.setLineWidth(0.25)
    gap, ln = bleed + 1.0, 3.0
    for x in (0, w):
        s = -1 if x == 0 else 1
        for y in (0, h):
            t = -1 if y == 0 else 1
            c.line((x + s * gap) * mm, y * mm, (x + s * (gap + ln)) * mm, y * mm)
            c.line(x * mm, (y + t * gap) * mm, x * mm, (y + t * (gap + ln)) * mm)


def render(path, w, h, front_fn, back_fn, card, title, bleed=0.0, margin=8.0):
    """bleed == 0: screen/digital PDF (rounded card). bleed > 0: print PDF with bleed + crop marks on a larger sheet."""
    global BLEED
    BLEED = bleed
    prep_logo()
    sw, sh = (w + 2 * margin, h + 2 * margin) if bleed else (w, h)
    c = canvas.Canvas(path, pagesize=(sw * mm, sh * mm))
    c.setTitle(title)
    c.setAuthor('Lumina Africa Foundation')
    for fn in (front_fn, back_fn):
        c.saveState()
        if bleed:
            c.translate(margin * mm, margin * mm)
        fn(c, card)
        if bleed:
            crop_marks(c, w, h, margin, bleed)
        c.restoreState()
        if bleed:
            tb = (margin * mm, margin * mm, (margin + w) * mm, (margin + h) * mm)
            bb = ((margin - bleed) * mm, (margin - bleed) * mm, (margin + w + bleed) * mm, (margin + h + bleed) * mm)
            for name, box in (('setTrimBox', tb), ('setBleedBox', bb)):
                if hasattr(c, name):
                    getattr(c, name)(box)
        c.showPage()
    c.save()
    BLEED = 0.0
    print('wrote', path)


def rect(c, x, y, w, h, col, r=0):
    c.setFillColor(col)
    if r:
        c.roundRect(x * mm, y * mm, w * mm, h * mm, r * mm, stroke=0, fill=1)
    else:
        c.rect(x * mm, y * mm, w * mm, h * mm, stroke=0, fill=1)


def poly(c, pts, col):
    p = c.beginPath()
    p.moveTo(pts[0][0] * mm, pts[0][1] * mm)
    for x, y in pts[1:]:
        p.lineTo(x * mm, y * mm)
    p.close()
    c.setFillColor(col)
    c.drawPath(p, stroke=0, fill=1)


def text(c, x, y, s, font, size, col, align='l', sp=0):
    c.setFillColor(col)
    w = c.stringWidth(s, font, size) + sp * len(s)
    x0 = x * mm - (w / 2 if align == 'c' else w if align == 'r' else 0)
    t = c.beginText()
    t.setTextOrigin(x0, y * mm)
    t.setFont(font, size)
    t.setCharSpace(sp)
    t.textOut(s)
    c.drawText(t)


def barcode(c, x, y, w, h, value):
    b = Code128(value, barWidth=0.4, barHeight=10, quiet=0, humanReadable=False)
    pad = 1.2
    rect(c, x, y, w, h, white, 0.6)
    c.saveState()
    c.translate((x + pad) * mm, (y + 0.8) * mm)
    c.scale((w - 2 * pad) * mm / b.width, (h - 1.6) * mm / 10)
    c.setFillColor(INK)
    b.drawOn(c, 0, 0)
    c.restoreState()


def icon(c, kind, cx, cy):
    c.setFillColor(NAVY)
    c.circle(cx * mm, cy * mm, 2.3 * mm, stroke=0, fill=1)
    c.setFillColor(white)
    c.setStrokeColor(white)
    c.setLineWidth(0.25 * mm)
    if kind == 'person':
        c.circle(cx * mm, (cy + 0.55) * mm, 0.6 * mm, stroke=0, fill=1)
        p = c.beginPath()
        p.moveTo((cx - 1.1) * mm, (cy - 1.1) * mm)
        p.curveTo((cx - 1.1) * mm, (cy - 0.1) * mm, (cx + 1.1) * mm, (cy - 0.1) * mm, (cx + 1.1) * mm, (cy - 1.1) * mm)
        p.close()
        c.drawPath(p, stroke=0, fill=1)
    elif kind == 'bars':
        for i, h in enumerate((0.9, 1.5, 2.1)):
            c.rect((cx - 1.2 + i * 0.85) * mm, (cy - 1.0) * mm, 0.6 * mm, h * mm, stroke=0, fill=1)
    else:
        c.roundRect((cx - 1.1) * mm, (cy - 1.0) * mm, 2.2 * mm, 2.0 * mm, 0.3 * mm, stroke=1, fill=0)
        c.line((cx - 1.1) * mm, (cy + 0.35) * mm, (cx + 1.1) * mm, (cy + 0.35) * mm)


def photo_block(c, px, py, pw, ph):
    rect(c, px - 0.4, py - 0.4, pw + 0.8, ph + 0.8, GOLD, 2)
    c.saveState()
    q = c.beginPath()
    q.roundRect(px * mm, py * mm, pw * mm, ph * mm, 1.6 * mm)
    c.clipPath(q, stroke=0, fill=0)
    if os.path.exists(PHOTO):
        im = Image.open(PHOTO)
        s = max(pw / im.width, ph / im.height)
        c.drawImage(PHOTO, (px + (pw - im.width * s) / 2) * mm, (py + (ph - im.height * s) / 2) * mm,
                    im.width * s * mm, im.height * s * mm)
    else:
        rect(c, px, py, pw, ph, HexColor('#E3E8F0'))
        cx = px + pw / 2
        c.setFillColor(HexColor('#B4BECF'))
        c.circle(cx * mm, (py + 15.6) * mm, 4.2 * mm, stroke=0, fill=1)
        p = c.beginPath()
        p.moveTo((cx - 8.2) * mm, py * mm)
        p.curveTo((cx - 8.2) * mm, (py + 9.5) * mm, (cx + 8.2) * mm, (py + 9.5) * mm, (cx + 8.2) * mm, py * mm)
        p.close()
        c.drawPath(p, stroke=0, fill=1)
        text(c, cx, py + 2.2, 'PHOTO', FB, 4, HexColor('#7F8CA3'), 'c', 0.8)
    c.restoreState()


def front(c, d):
    c.saveState()
    clip_card(c)
    full(c, white)
    c.drawImage(LOGO_FAINT, 54 * mm, 11 * mm, 30 * mm, 30 * mm, mask='auto')
    # header: white, with a slim gold edge
    sl = 2.9 / W
    poly(c, [(-BLEED, 39.0 - sl * BLEED), (W + BLEED, 41.9 + sl * BLEED), (W + BLEED, 42.5 + sl * BLEED), (-BLEED, 39.6 - sl * BLEED)], GOLD)
    c.drawImage(LOGO, 3.6 * mm, 41.4 * mm, 11.6 * mm, 11.6 * mm, mask='auto')
    text(c, 17.6, 48.6, 'LUMINA AFRICA', FB, 11, NAVY, sp=0.25)
    text(c, 17.6, 44.9, 'FOUNDATION', FB, 7.4, GOLD_TXT, sp=1.35)
    text(c, 17.6, 42.2, 'Igniting Light. Inspiring Change.', F, 4.3, SOFT)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.25 * mm)
    c.roundRect(65 * mm, 47.4 * mm, 17.4 * mm, 4.4 * mm, 2.2 * mm, stroke=1, fill=0)
    text(c, 73.7, 48.9, 'OFFICIAL ID', FB, 4.1, GOLD_TXT, 'c', 0.5)
    photo_block(c, 4, 13.8, 19, 23.4)
    # id number + barcode
    text(c, 14.5, 9.6, 'ID NUMBER', FB, 3.5, SOFT, 'c', 0.8)
    text(c, 14.5, 6.6, d['idno'], FB, 7, NAVY, 'c', 0.4)
    barcode(c, 3.6, 1.0, 21.8, 4.0, d['idno'])
    # details
    c.setStrokeColor(HexColor('#D8D2C5'))
    c.setLineWidth(0.2 * mm)
    c.line(29.8 * mm, 14 * mm, 29.8 * mm, 37 * mm)
    text(c, 32.6, 33.0, d['name'], FB, 9.6, NAVY)
    text(c, 32.6, 29.3, d['role'], FB, 5.3, GOLD_TXT, sp=0.3)
    c.setStrokeColor(HexColor('#CFC7B8'))
    c.line(32.6 * mm, 27.6 * mm, 82 * mm, 27.6 * mm)
    rows = (('person', 'DEPARTMENT', d['dept']), ('bars', 'PROGRAMME / LOCATION', d['prog']), ('cal', 'VALID UNTIL', d['valid']))
    for i, (k, lab, val) in enumerate(rows):
        y = 23.2 - i * 6.2
        icon(c, k, 34.9, y)
        text(c, 38.6, y + 0.9, lab, FB, 3.6, NAVY, sp=0.4)
        text(c, 38.6, y - 1.9, val, F, 5.2, INK)
    # signature + issue date
    c.setStrokeColor(INK)
    c.setLineWidth(0.2 * mm)
    c.line(32.6 * mm, 8.3 * mm, 56 * mm, 8.3 * mm)
    text(c, 32.6, 6.4, 'AUTHORISED SIGNATURE', F, 3.3, SOFT, sp=0.3)
    c.setStrokeColor(HexColor('#CFC7B8'))
    c.line(60.5 * mm, 5.8 * mm, 60.5 * mm, 10.6 * mm)
    text(c, 63, 9.6, 'ISSUE DATE', FB, 3.5, SOFT, sp=0.4)
    text(c, 63, 6.5, d['issued'], FB, 5.2, NAVY)
    # footer
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.25 * mm)
    c.line(32.6 * mm, 3.9 * mm, 82 * mm, 3.9 * mm)
    text(c, 57.3, 1.5, 'IGNITING LIGHT   \u2022   INSPIRING CHANGE', FB, 3.4, GOLD_TXT, 'c', 0.9)
    c.restoreState()


def back(c, d=None):
    c.saveState()
    clip_card(c)
    full(c, white)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.3 * mm)
    c.roundRect(2 * mm, 2 * mm, (W - 4) * mm, (H - 4) * mm, 2.2 * mm, stroke=1, fill=0)
    c.drawImage(LOGO, 5.5 * mm, 30.5 * mm, 17 * mm, 17 * mm, mask='auto')
    text(c, 26, 41.4, 'LUMINA AFRICA', FB, 9, NAVY, sp=0.25)
    text(c, 26, 37.4, 'FOUNDATION', FB, 6.4, GOLD_TXT, sp=1.2)
    text(c, 26, 34.2, 'Igniting Light. Inspiring Change.', F, 4.2, SOFT)
    text(c, 6, 27.4, 'IF FOUND, PLEASE RETURN TO', FB, 3.8, GOLD_TXT, sp=0.6)
    for i, s in enumerate(('Lumina Africa Foundation', ADDRESS[0] + ', ' + ADDRESS[1], PHONE + '  •  ' + EMAIL, WEB)):
        text(c, 6, 24 - i * 3.1, s, F, 4.4, INK)
    text(c, 6, 11.4, 'This card remains the property of Lumina Africa Foundation, is non-transferable and must be', F, 3.3, SOFT)
    text(c, 6, 8.9, 'surrendered on request or when the holder leaves. Misuse may result in action being taken.', F, 3.3, SOFT)
    qr(c, 62.6, 27.6, 17.4)
    text(c, 71.3, 25.2, 'SCAN TO VERIFY', FB, 3.2, NAVY, 'c', 0.5)
    c.setStrokeColor(HexColor('#CFC7B8'))
    c.setLineWidth(0.2 * mm)
    c.roundRect(55.5 * mm, 13.6 * mm, 24.1 * mm, 8.6 * mm, 0.8 * mm, stroke=1, fill=0)
    text(c, 67.5, 14.9, "HOLDER'S SIGNATURE", FB, 3.1, SOFT, 'c', 0.3)
    text(c, 6, 4.9, REG, F, 3.3, GOLD_TXT)
    c.restoreState()


def build(bleed=0.0):
    out = OUT.replace('.pdf', '-print.pdf') if bleed else OUT
    render(out, W, H, front, back, CARD, 'Lumina Africa Foundation - ID card', bleed)


if __name__ == '__main__':
    build()
    build(bleed=3.0)
