"""Lumina Africa Foundation ID card, PORTRAIT / lanyard format (CR80 vertical, 54 x 85.6 mm). Front + back -> PDF.
Run: "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" docs/brand/id-card/build_id_card_portrait.py
Reuses the helpers and brand tokens from build_id_card.py. Edit CARD for a real holder; photo.jpg (if present) replaces the placeholder.
"""
import os
from PIL import Image
from reportlab.pdfgen import canvas
from reportlab.lib.units import mm
from reportlab.lib.colors import HexColor, white
import build_id_card as b
from build_id_card import NAVY, GOLD, GOLD_TXT, INK, SOFT, CREAM, F, FB, rect, text, barcode, qr, full, render

W, H = 54.0, 85.6
CX = W / 2
OUT = os.path.join(b.HERE, 'lumina-id-card-portrait.pdf')
CARD = dict(name='FULL NAME', role='ROLE / POSITION', dept='Department or programme',
            valid='DD MMM YYYY', issued='DD MMM YYYY', idno='LAF-ID-0001')
LINE = HexColor('#CFC7B8')


def fit(s, font, size, maxw):
    while size > 4 and b.pdfmetrics.stringWidth(s, font, size) > maxw * mm:
        size -= 0.2
    return size


def clip(c):
    b.clip_card(c, W, H)


def wave(c):
    """Soft cream cap behind the header with a gold curve along its lower edge (runs into the bleed when printing)."""
    B = b.BLEED
    p = c.beginPath()
    p.moveTo(-B * mm, (H + B) * mm)
    p.lineTo((W + B) * mm, (H + B) * mm)
    p.lineTo((W + B) * mm, 58 * mm)
    p.lineTo(W * mm, 58 * mm)
    p.curveTo(40 * mm, 54 * mm, 14 * mm, 66 * mm, 0, 60 * mm)
    p.lineTo(-B * mm, 60 * mm)
    p.close()
    c.setFillColor(CREAM)
    c.drawPath(p, stroke=0, fill=1)
    q = c.beginPath()
    q.moveTo((W + B) * mm, 58 * mm)
    q.lineTo(W * mm, 58 * mm)
    q.curveTo(40 * mm, 54 * mm, 14 * mm, 66 * mm, 0, 60 * mm)
    q.lineTo(-B * mm, 60 * mm)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.5 * mm)
    c.drawPath(q, stroke=1, fill=0)


def photo(c, cx, cy, r):
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.6 * mm)
    c.circle(cx * mm, cy * mm, (r + 1.2) * mm, stroke=1, fill=0)
    c.saveState()
    p = c.beginPath()
    p.circle(cx * mm, cy * mm, r * mm)
    c.clipPath(p, stroke=0, fill=0)
    if os.path.exists(b.PHOTO):
        im = Image.open(b.PHOTO)
        s = max(2 * r / im.width, 2 * r / im.height)
        c.drawImage(b.PHOTO, (cx - im.width * s / 2) * mm, (cy - im.height * s / 2) * mm, im.width * s * mm, im.height * s * mm)
    else:
        rect(c, cx - r, cy - r, 2 * r, 2 * r, HexColor('#E9EDF3'))
        c.setFillColor(HexColor('#B4BECF'))
        c.circle(cx * mm, (cy + 3.6) * mm, 4.6 * mm, stroke=0, fill=1)
        q = c.beginPath()
        q.moveTo((cx - 9.5) * mm, (cy - r) * mm)
        q.curveTo((cx - 9.5) * mm, (cy - 3.4) * mm, (cx + 9.5) * mm, (cy - 3.4) * mm, (cx + 9.5) * mm, (cy - r) * mm)
        q.close()
        c.drawPath(q, stroke=0, fill=1)
        text(c, cx, cy - r + 3.2, 'PHOTO', FB, 3.8, HexColor('#7F8CA3'), 'c', 0.8)
    c.restoreState()


def front(c, d):
    c.saveState()
    clip(c)
    full(c, white, W, H)
    wave(c)
    # lanyard slot guide
    c.setStrokeColor(HexColor('#D8D2C5'))
    c.setLineWidth(0.2 * mm)
    c.roundRect((CX - 5) * mm, 81.6 * mm, 10 * mm, 1.8 * mm, 0.9 * mm, stroke=1, fill=0)
    c.drawImage(b.LOGO, (CX - 5.2) * mm, 70.2 * mm, 10.4 * mm, 10.4 * mm, mask='auto')
    text(c, CX, 67.3, 'LUMINA AFRICA', FB, 6.6, NAVY, 'c', 0.3)
    text(c, CX, 64.3, 'FOUNDATION', FB, 4.4, GOLD_TXT, 'c', 1.2)
    photo(c, CX, 46.6, 13.4)
    # identity
    s = fit(d['name'], FB, 10, 46)
    text(c, CX, 27.0, d['name'], FB, s, NAVY, 'c')
    s = fit(d['role'], FB, 5.2, 46)
    text(c, CX, 22.8, d['role'], FB, s, GOLD_TXT, 'c', 0.4)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.4 * mm)
    c.line((CX - 5) * mm, 20.4 * mm, (CX + 5) * mm, 20.4 * mm)
    text(c, CX, 17.6, 'DEPARTMENT', FB, 3.2, SOFT, 'c', 0.5)
    text(c, CX, 14.8, d['dept'], F, 5, INK, 'c')
    text(c, 15, 11.0, 'ISSUED', FB, 3.2, SOFT, 'c', 0.5)
    text(c, 15, 8.4, d['issued'], F, 4.8, INK, 'c')
    text(c, 39, 11.0, 'VALID UNTIL', FB, 3.2, SOFT, 'c', 0.5)
    text(c, 39, 8.4, d['valid'], F, 4.8, INK, 'c')
    c.setStrokeColor(LINE)
    c.setLineWidth(0.2 * mm)
    c.line(CX * mm, 7.6 * mm, CX * mm, 11.8 * mm)
    # barcode + id number, gold base bar
    barcode(c, 7, 2.3, 40, 4.4, d['idno'])
    rect(c, -b.BLEED, -b.BLEED, W + 2 * b.BLEED, 1.2 + b.BLEED, GOLD)
    c.restoreState()


def back(c, d):
    c.saveState()
    clip(c)
    full(c, white, W, H)
    c.setStrokeColor(GOLD)
    c.setLineWidth(0.3 * mm)
    c.roundRect(2 * mm, 2 * mm, (W - 4) * mm, (H - 4) * mm, 2.2 * mm, stroke=1, fill=0)
    c.drawImage(b.LOGO, (CX - 6) * mm, 68.4 * mm, 12 * mm, 12 * mm, mask='auto')
    text(c, CX, 64.8, 'LUMINA AFRICA', FB, 6.6, NAVY, 'c', 0.3)
    text(c, CX, 61.9, 'FOUNDATION', FB, 4.4, GOLD_TXT, 'c', 1.2)
    text(c, CX, 59.3, 'Igniting Light. Inspiring Change.', F, 3.4, SOFT, 'c')
    qr(c, CX - 8.5, 39.2, 17)
    text(c, CX, 36.6, 'SCAN TO VERIFY', FB, 3.1, NAVY, 'c', 0.5)
    text(c, CX, 32.6, 'IF FOUND, PLEASE RETURN TO', FB, 3.1, GOLD_TXT, 'c', 0.5)
    for i, s in enumerate((b.ADDRESS[0] + ', ' + b.ADDRESS[1], b.PHONE, b.WEB)):
        text(c, CX, 29.6 - i * 2.9, s, F, 3.7, INK, 'c')
    c.setStrokeColor(LINE)
    c.setLineWidth(0.2 * mm)
    c.roundRect(7 * mm, 12.6 * mm, 40 * mm, 8.0 * mm, 0.8 * mm, stroke=1, fill=0)
    text(c, CX, 14.0, "HOLDER'S SIGNATURE", FB, 3, SOFT, 'c', 0.3)
    text(c, CX, 9.4, 'Property of Lumina Africa Foundation. Non-transferable.', F, 2.9, SOFT, 'c')
    text(c, CX, 6.2, b.REG, F, 3.1, GOLD_TXT, 'c')
    rect(c, -b.BLEED, -b.BLEED, W + 2 * b.BLEED, 1.2 + b.BLEED, GOLD)
    c.restoreState()


def build(bleed=0.0):
    out = OUT.replace('.pdf', '-print.pdf') if bleed else OUT
    render(out, W, H, front, back, CARD, 'Lumina Africa Foundation - ID card (portrait)', bleed)


if __name__ == '__main__':
    build()
    build(bleed=3.0)
