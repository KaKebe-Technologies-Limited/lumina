"""
Generates the SAMPLE product pictures of the shop into assets/img/shop/ (flat, vector-style mockups in the Lumina navy / gold / orange, using the real
logo patch assets/img/brand/logo-512.png as the print). They are PLACEHOLDERS, not photographs: the owner replaces them with real photos in Admin > Shop.

    python bin/dev/make-shop-samples.py          (plain "python" = Python 3 with Pillow; no other dependency)

Output per picture (name = e.g. hoodie-navy-front):  name.jpg (600x600)  name-2x.jpg (1200x1200)  name.webp  name-2x.webp ; plus shop-hero.jpg / .webp (1600x900).
Every file is kept under 150 KB. The script is deterministic: running it again rewrites identical files.
"""
import math
import os
import sys

from PIL import Image, ImageChops, ImageDraw, ImageFilter, ImageFont

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
OUT = os.path.join(ROOT, "assets", "img", "shop")
LOGO = os.path.join(ROOT, "assets", "img", "brand", "logo-512.png")

S = 2400                      # working size (final 1200 and 600): supersampling for smooth edges
NAVY = (44, 57, 80)
NAVY_DARK = (30, 41, 62)
NAVY_FOLD = (36, 48, 70)
NAVY_HI = (58, 73, 99)
GOLD = (214, 161, 81)
GOLD_DARK = (184, 134, 56)
GOLD_FOLD = (196, 146, 66)
GOLD_HI = (226, 178, 104)
ORANGE = (204, 101, 26)
CREAM = (240, 231, 211)
CREAM_FOLD = (222, 210, 186)
CREAM_HI = (248, 242, 228)
BG_TOP = (245, 242, 236)
BG_BOT = (233, 228, 218)


def font(size, bold=True, serif=False):
    names = (["georgiab.ttf", "georgia.ttf"] if serif else ["arialbd.ttf", "arial.ttf"]) if bold else ["arial.ttf"]
    for n in names:
        p = os.path.join("C:/Windows/Fonts", n)
        if os.path.exists(p):
            return ImageFont.truetype(p, size)
    return ImageFont.load_default()


def px(v):
    return v * S / 1000.0


def pts(points):
    return [(px(x), px(y)) for x, y in points]


def bez(p0, p1, p2, p3, n=28):
    out = []
    for i in range(1, n + 1):
        t = i / n
        a = (1 - t) ** 3
        b = 3 * (1 - t) ** 2 * t
        c = 3 * (1 - t) * t ** 2
        d = t ** 3
        out.append((a * p0[0] + b * p1[0] + c * p2[0] + d * p3[0], a * p0[1] + b * p1[1] + c * p2[1] + d * p3[1]))
    return out


def quad(p0, p1, p2, n=24):
    out = []
    for i in range(1, n + 1):
        t = i / n
        out.append(((1 - t) ** 2 * p0[0] + 2 * (1 - t) * t * p1[0] + t ** 2 * p2[0], (1 - t) ** 2 * p0[1] + 2 * (1 - t) * t * p1[1] + t ** 2 * p2[1]))
    return out


def build(start, segs):
    """segs: ('L', p) | ('Q', c, p) | ('C', c1, c2, p)  in the 1000 grid -> list of points"""
    pts_ = [start]
    cur = start
    for s in segs:
        if s[0] == "L":
            pts_.append(s[1])
            cur = s[1]
        elif s[0] == "Q":
            pts_.extend(quad(cur, s[1], s[2]))
            cur = s[2]
        elif s[0] == "C":
            pts_.extend(bez(cur, s[1], s[2], s[3]))
            cur = s[3]
    return pts_


def mask_of(poly):
    m = Image.new("L", (S, S), 0)
    ImageDraw.Draw(m).polygon(pts(poly), fill=255)
    return m


def layer():
    return Image.new("RGBA", (S, S), (0, 0, 0, 0))


def fill_poly(img, poly, color):
    ImageDraw.Draw(img).polygon(pts(poly), fill=color + (255,) if len(color) == 3 else color)


def shade(img, mask, base, light, dark):
    """soft diagonal light across a shape: light top-left, dark bottom-right, clipped to mask"""
    grad = Image.new("RGB", (S, S), base)
    g = ImageDraw.Draw(grad)
    for i in range(0, S, 4):
        t = i / S
        if t < 0.5:
            k = (0.5 - t) / 0.5 * 0.55
            c = tuple(int(base[j] + (light[j] - base[j]) * k) for j in range(3))
        else:
            k = (t - 0.5) / 0.5 * 0.7
            c = tuple(int(base[j] + (dark[j] - base[j]) * k) for j in range(3))
        g.rectangle([0, i, S, i + 4], fill=c)
    grad = grad.rotate(-18, resample=Image.BICUBIC, expand=False, fillcolor=base)
    img.paste(grad, (0, 0), mask)


def line(img, points, color, width):
    ImageDraw.Draw(img).line(pts(points), fill=color + (255,), width=int(px(width)), joint="curve")


def shadow_under(canvas, mask, dy=18, blur=26, alpha=70):
    sh = Image.new("RGBA", (S, S), (0, 0, 0, 0))
    solid = Image.new("RGBA", (S, S), (30, 30, 30, alpha))
    off = ImageChops.offset(mask, 0, int(px(dy)))
    sh.paste(solid, (0, 0), off)
    sh = sh.filter(ImageFilter.GaussianBlur(px(blur)))
    canvas.alpha_composite(sh)


def logo_patch(size_grid):
    lg = Image.open(LOGO).convert("RGBA")
    d = int(px(size_grid))
    lg = lg.resize((d, d), Image.LANCZOS)
    # soft drop shadow so the patch sits on the cloth
    pad = int(d * 0.12)
    sh = Image.new("RGBA", (d + pad * 2, d + pad * 2), (0, 0, 0, 0))
    a = lg.split()[3].point(lambda v: int(v * 0.45))
    blk = Image.new("RGBA", lg.size, (0, 0, 0, 255))
    sh.paste(blk, (pad, pad + int(d * 0.025)), a)
    sh = sh.filter(ImageFilter.GaussianBlur(d * 0.025))
    sh.alpha_composite(lg, (pad, pad))
    return sh


def place(img, patch, cx, cy, angle=0):
    p = patch.rotate(angle, resample=Image.BICUBIC, expand=True) if angle else patch
    img.alpha_composite(p, (int(px(cx) - p.width / 2), int(px(cy) - p.height / 2)))


def text_center(img, text, cx, cy, size, color, serif=False, spacing=0, rotate=0):
    f = font(int(px(size)), True, serif)
    # letter-spaced text drawn on its own layer
    w = 0
    widths = []
    for ch in text:
        cw = f.getlength(ch)
        widths.append(cw)
        w += cw + spacing * S / 1000.0
    w -= spacing * S / 1000.0
    lay = Image.new("RGBA", (int(w) + 40, int(px(size)) * 2), (0, 0, 0, 0))
    d = ImageDraw.Draw(lay)
    x = 20
    for ch, cw in zip(text, widths):
        d.text((x, int(px(size) * 0.2)), ch, font=f, fill=color + (255,))
        x += cw + spacing * S / 1000.0
    if rotate:
        lay = lay.rotate(rotate, resample=Image.BICUBIC, expand=True)
    img.alpha_composite(lay, (int(px(cx) - lay.width / 2), int(px(cy) - lay.height / 2)))


def mirror_poly(poly):
    return [(1000 - x, y) for x, y in poly]


# ------------------------------------------------------------------------------------------------------------ garments
def tshirt(color, fold, hi, back=False, text_color=GOLD):
    img = layer()
    outline = build((392, 150), [
        ("L", (262, 196)), ("L", (86, 322)), ("L", (166, 436)), ("L", (258, 384)),
        ("Q", (246, 640), (262, 866)), ("Q", (500, 884), (738, 866)),
        ("Q", (754, 640), (742, 384)), ("L", (834, 436)), ("L", (914, 322)), ("L", (738, 196)), ("L", (608, 150)),
    ] + ([("Q", (500, 168), (392, 150))] if back else [("Q", (500, 318), (392, 150))]))
    m = mask_of(outline)
    shadow_under(img, m)
    shade(img, m, color, hi, fold)
    # seams and folds
    line(img, [(258, 384), (292, 232)], fold, 5)
    line(img, [(742, 384), (708, 232)], fold, 5)
    line(img, [(262, 842), (738, 842)], fold, 5)
    # collar
    inner = build((392, 150), [("Q", (500, 126), (608, 150)), ("Q", (500, 318), (392, 150))]) if not back else build((392, 150), [("Q", (500, 128), (608, 150)), ("Q", (500, 168), (392, 150))])
    ImageDraw.Draw(img).polygon(pts(inner), fill=fold + (255,))
    rib = build((392, 150), [("Q", (500, 318), (608, 150))]) if not back else build((392, 150), [("Q", (500, 172), (608, 150))])
    line(img, rib, hi if color != CREAM else CREAM_FOLD, 26)
    return img


def hoodie(color, fold, hi, back=False):
    img = layer()
    body = build((262, 224), [
        ("L", (316, 214)), ("Q", (500, 262), (684, 214)), ("L", (738, 224)),
        ("Q", (790, 236), (816, 300)), ("L", (930, 770)), ("L", (836, 796)), ("L", (748, 470)),
        ("L", (748, 864)), ("Q", (500, 884), (252, 864)), ("L", (252, 470)),
        ("L", (164, 796)), ("L", (70, 770)), ("L", (184, 300)), ("Q", (210, 236), (262, 224)),
    ])
    m = mask_of(body)
    shadow_under(img, m)
    shade(img, m, color, hi, fold)
    # hood
    hood = build((318, 214), [("C", (250, 150), (290, 40), (500, 36)), ("C", (710, 40), (750, 150), (682, 214)), ("Q", (500, 270), (318, 214))])
    hm = mask_of(hood)
    shade(img, hm, color, hi, fold)
    if back:
        inner = build((352, 196), [("Q", (500, 240), (648, 196)), ("Q", (500, 120), (352, 196))])
        ImageDraw.Draw(img).polygon(pts(inner), fill=fold + (255,))
        line(img, build((330, 214), [("Q", (500, 268), (670, 214))]), fold, 6)
    else:
        opening = build((372, 200), [("C", (372, 100), (440, 82), (500, 82)), ("C", (560, 82), (628, 100), (628, 200)), ("Q", (500, 296), (372, 200))])
        ImageDraw.Draw(img).polygon(pts(opening), fill=NAVY_DARK + (255,) if color == NAVY else fold + (255,))
        line(img, opening + [opening[0]], hi, 9)
        # drawstrings
        for dx in (-34, 34):
            line(img, [(500 + dx, 262), (500 + dx * 1.1, 330), (500 + dx * 1.15, 410)], CREAM_HI, 9)
            line(img, [(500 + dx * 1.15, 410), (500 + dx * 1.15, 440)], GOLD, 16)
    # cuffs + hem ribbing
    ImageDraw.Draw(img).polygon(pts([(70, 770), (164, 796), (170, 842), (80, 818)]), fill=fold + (255,))
    ImageDraw.Draw(img).polygon(pts([(930, 770), (836, 796), (830, 842), (920, 818)]), fill=fold + (255,))
    ImageDraw.Draw(img).polygon(pts([(252, 820), (748, 820), (748, 866), (500, 884), (252, 866)]), fill=fold + (255,))
    for i in range(0, 10):
        x = 275 + i * 50
        line(img, [(x, 826), (x - 4, 872)], hi, 3)
    # sleeve / body seams
    line(img, [(252, 470), (216, 300)], fold, 5)
    line(img, [(748, 470), (784, 300)], fold, 5)
    if not back:
        pocket = build((338, 600), [("L", (662, 600)), ("L", (708, 806)), ("Q", (500, 822), (292, 806)), ("L", (338, 600))])
        pm = mask_of(pocket)
        shade(img, pm, color, hi, fold)
        line(img, pocket + [pocket[0]], fold, 6)
    return img


def sweatshirt(color, fold, hi, back=False):
    img = layer()
    body = build((262, 214), [
        ("L", (400, 168)), ("Q", (500, 230), (600, 168)), ("L", (738, 214)),
        ("Q", (792, 232), (818, 300)), ("L", (930, 770)), ("L", (836, 796)), ("L", (748, 470)),
        ("L", (748, 864)), ("Q", (500, 884), (252, 864)), ("L", (252, 470)),
        ("L", (164, 796)), ("L", (70, 770)), ("L", (182, 300)), ("Q", (208, 232), (262, 214)),
    ])
    m = mask_of(body)
    shadow_under(img, m)
    shade(img, m, color, hi, fold)
    ImageDraw.Draw(img).polygon(pts([(70, 770), (164, 796), (170, 842), (80, 818)]), fill=fold + (255,))
    ImageDraw.Draw(img).polygon(pts([(930, 770), (836, 796), (830, 842), (920, 818)]), fill=fold + (255,))
    ImageDraw.Draw(img).polygon(pts([(252, 820), (748, 820), (748, 866), (500, 884), (252, 866)]), fill=fold + (255,))
    for i in range(0, 10):
        x = 275 + i * 50
        line(img, [(x, 826), (x - 4, 872)], hi, 3)
    line(img, [(252, 470), (216, 300)], fold, 5)
    line(img, [(748, 470), (784, 300)], fold, 5)
    # crew neck
    ImageDraw.Draw(img).polygon(pts(build((398, 168), [("Q", (500, 128), (602, 168)), ("Q", (500, 248), (398, 168))]) if back else build((398, 168), [("Q", (500, 132), (602, 168)), ("Q", (500, 320), (398, 168))])), fill=fold + (255,))
    line(img, build((398, 168), [("Q", (500, 320), (602, 168))]) if not back else build((398, 168), [("Q", (500, 238), (602, 168))]), hi, 30)
    return img


def cap(color, fold, hi):
    img = layer()
    dome = build((200, 560), [("C", (200, 290), (330, 180), (500, 180)), ("C", (670, 180), (800, 290), (800, 560)), ("Q", (500, 620), (200, 560))])
    brim = build((168, 584), [("Q", (500, 524), (832, 584)), ("Q", (884, 652), (806, 706)), ("Q", (500, 770), (194, 706)), ("Q", (116, 652), (168, 584))])
    both = Image.new("L", (S, S), 0)
    ImageDraw.Draw(both).polygon(pts(dome), fill=255)
    ImageDraw.Draw(both).polygon(pts(brim), fill=255)
    shadow_under(img, both, dy=24, blur=30, alpha=80)
    bm = mask_of(brim)
    shade(img, bm, fold, color, NAVY_DARK if color == NAVY else GOLD_DARK)
    line(img, build((160, 600), [("Q", (500, 540), (840, 600))]), color, 12)
    dm = mask_of(dome)
    shade(img, dm, color, hi, fold)
    # panel seams from the button
    for end in [(300, 560), (400, 575), (600, 575), (700, 560)]:
        line(img, build((500, 196), [("Q", ((500 + end[0]) / 2, 330), end)]), fold, 5)
    # button + eyelets
    ImageDraw.Draw(img).ellipse([px(478), px(172), px(522), px(204)], fill=fold + (255,))
    for ex in (330, 670):
        ImageDraw.Draw(img).ellipse([px(ex - 9), px(318 - 7), px(ex + 9), px(318 + 7)], fill=hi + (255,))
    # band under dome
    line(img, build((205, 556), [("Q", (500, 620), (795, 556))]), fold, 10)
    return img


def sticker_sheet():
    img = layer()
    sheet = build((150, 110), [("L", (850, 110)), ("Q", (890, 110), (890, 150)), ("L", (890, 850)), ("Q", (890, 890), (850, 890)), ("L", (150, 890)), ("Q", (110, 890), (110, 850)), ("L", (110, 150)), ("Q", (110, 110), (150, 110))])
    m = mask_of(sheet)
    shadow_under(img, m, dy=14, blur=22, alpha=60)
    ImageDraw.Draw(img).polygon(pts(sheet), fill=(250, 249, 246, 255))
    d = ImageDraw.Draw(img)
    # round logo sticker (die-cut with white border)
    place(img, logo_patch(330), 330, 330)
    # navy rounded label
    d.rounded_rectangle([px(520), px(210), px(840), px(360)], radius=px(30), fill=NAVY + (255,))
    d.rounded_rectangle([px(532), px(222), px(828), px(348)], radius=px(24), outline=GOLD + (255,), width=int(px(5)))
    text_center(img, "WEAR THE", 680, 262, 48, GOLD, spacing=3)
    text_center(img, "MISSION", 680, 316, 54, (255, 255, 255), spacing=5)
    # gold circle
    d.ellipse([px(150), px(560), px(400), px(810)], fill=GOLD + (255,))
    d.ellipse([px(168), px(578), px(382), px(792)], outline=NAVY + (255,), width=int(px(6)))
    text_center(img, "LUMINA", 275, 662, 46, NAVY, serif=True, spacing=2)
    text_center(img, "AFRICA", 275, 724, 30, NAVY, spacing=6)
    # flame sticker
    flame = build((600, 570), [("C", (520, 640), (500, 720), (560, 780)), ("C", (590, 805), (640, 805), (670, 775)), ("C", (720, 725), (700, 650), (650, 610)), ("C", (660, 650), (640, 680), (620, 690)), ("C", (630, 650), (620, 600), (600, 570))])
    fm = mask_of(flame)
    halo = fm.filter(ImageFilter.MaxFilter(21))
    img.paste(Image.new("RGBA", (S, S), (255, 255, 255, 255)), (0, 0), halo)
    img.paste(Image.new("RGBA", (S, S), ORANGE + (255,)), (0, 0), fm)
    # pill
    d.rounded_rectangle([px(450), px(830), px(850), px(868)], radius=px(19), fill=ORANGE + (255,))
    text_center(img, "IGNITING LIGHT", 650, 849, 24, (255, 255, 255), spacing=5)
    # star
    star = []
    for i in range(10):
        r = 78 if i % 2 == 0 else 34
        a = -math.pi / 2 + i * math.pi / 5
        star.append((810 + r * math.cos(a) - 0, 560 + r * math.sin(a) + 120))
    sm = mask_of(star)
    img.paste(Image.new("RGBA", (S, S), (255, 255, 255, 255)), (0, 0), sm.filter(ImageFilter.MaxFilter(15)))
    img.paste(Image.new("RGBA", (S, S), GOLD + (255,)), (0, 0), sm)
    return img


def sticker_single():
    img = layer()
    c = Image.new("L", (S, S), 0)
    ImageDraw.Draw(c).ellipse([px(190), px(190), px(810), px(810)], fill=255)
    shadow_under(img, c, dy=20, blur=28, alpha=70)
    ImageDraw.Draw(img).ellipse([px(190), px(190), px(810), px(810)], fill=(255, 255, 255, 255))
    place(img, logo_patch(560), 500, 500)
    return img.rotate(-8, resample=Image.BICUBIC, center=(S / 2, S / 2))


# ------------------------------------------------------------------------------------------------------------ scenes
def background(w=S, h=S):
    bg = Image.new("RGBA", (w, h))
    d = ImageDraw.Draw(bg)
    for y in range(h):
        t = y / h
        c = tuple(int(BG_TOP[i] + (BG_BOT[i] - BG_TOP[i]) * t) for i in range(3))
        d.line([(0, y), (w, y)], fill=c + (255,))
    # soft floor glow
    glow = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    ImageDraw.Draw(glow).ellipse([w * 0.12, h * 0.80, w * 0.88, h * 0.98], fill=(0, 0, 0, 22))
    bg.alpha_composite(glow.filter(ImageFilter.GaussianBlur(w * 0.03)))
    return bg


def scene(item):
    bg = background()
    bg.alpha_composite(item)
    return bg


def save(name, im):
    os.makedirs(OUT, exist_ok=True)
    rgb = im.convert("RGB")
    big = rgb.resize((1200, 1200), Image.LANCZOS)
    small = rgb.resize((600, 600), Image.LANCZOS)
    big.save(os.path.join(OUT, name + "-2x.jpg"), "JPEG", quality=84, optimize=True, progressive=True)
    small.save(os.path.join(OUT, name + ".jpg"), "JPEG", quality=84, optimize=True, progressive=True)
    big.save(os.path.join(OUT, name + "-2x.webp"), "WEBP", quality=82, method=6)
    small.save(os.path.join(OUT, name + ".webp"), "WEBP", quality=82, method=6)


def with_patch(item, cx, cy, size, angle=0):
    place(item, logo_patch(size), cx, cy, angle)
    return item


def main():
    # hoodie: front with the patch, back with the tagline
    h = hoodie(NAVY, NAVY_FOLD, NAVY_HI)
    with_patch(h, 500, 470, 190)
    save("hoodie-navy-front", scene(h))
    hb = hoodie(NAVY, NAVY_FOLD, NAVY_HI, back=True)
    text_center(hb, "IGNITING LIGHT,", 500, 470, 40, GOLD, spacing=4)
    text_center(hb, "INSPIRING CHANGE", 500, 524, 40, GOLD, spacing=4)
    with_patch(hb, 500, 650, 150)
    save("hoodie-navy-back", scene(hb))

    # crew-neck jumper in gold
    j = sweatshirt(GOLD, GOLD_FOLD, GOLD_HI)
    with_patch(j, 500, 440, 210)
    save("jumper-gold-front", scene(j))
    jb = sweatshirt(GOLD, GOLD_FOLD, GOLD_HI, back=True)
    text_center(jb, "LUMINA AFRICA", 500, 420, 54, NAVY, serif=True, spacing=6)
    text_center(jb, "FOUNDATION", 500, 490, 54, NAVY, serif=True, spacing=6)
    save("jumper-gold-back", scene(jb))

    # caps: navy and gold
    c1 = cap(NAVY, NAVY_FOLD, NAVY_HI)
    with_patch(c1, 500, 400, 175)
    save("cap-navy", scene(c1))
    c2 = cap(GOLD, GOLD_FOLD, GOLD_HI)
    with_patch(c2, 500, 400, 175)
    save("cap-gold", scene(c2))

    # T-shirts: navy and cream
    t1 = tshirt(NAVY, NAVY_FOLD, NAVY_HI)
    with_patch(t1, 500, 470, 200)
    save("tshirt-navy-front", scene(t1))
    t1b = tshirt(NAVY, NAVY_FOLD, NAVY_HI, back=True)
    text_center(t1b, "LUMINA AFRICA FOUNDATION", 500, 330, 30, GOLD, spacing=3)
    save("tshirt-navy-back", scene(t1b))
    t2 = tshirt(CREAM, CREAM_FOLD, CREAM_HI)
    with_patch(t2, 500, 470, 200)
    save("tshirt-cream-front", scene(t2))
    t2b = tshirt(CREAM, CREAM_FOLD, CREAM_HI, back=True)
    text_center(t2b, "WEAR THE MISSION", 500, 340, 36, NAVY, spacing=4)
    save("tshirt-cream-back", scene(t2b))

    # stickers
    save("stickers-sheet", scene(sticker_sheet()))
    save("stickers-single", scene(sticker_single()))

    hero()
    print("Wrote the sample pictures to", OUT)
    for f in sorted(os.listdir(OUT)):
        size = os.path.getsize(os.path.join(OUT, f))
        print("%-34s %6.1f KB" % (f, size / 1024.0))
        if size > 150 * 1024:
            print("  WARNING: larger than 150 KB")
            sys.exit(1)


def hero():
    W, H = 3200, 1800
    bg = Image.new("RGBA", (W, H))
    d = ImageDraw.Draw(bg)
    for y in range(H):
        t = y / H
        c = tuple(int(NAVY_DARK[i] + (NAVY[i] - NAVY_DARK[i]) * t) for i in range(3))
        d.line([(0, y), (W, y)], fill=c + (255,))
    # large soft gold glow behind the products
    glow = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    ImageDraw.Draw(glow).ellipse([W * 0.30, H * 0.05, W * 1.0, H * 1.05], fill=(96, 118, 160, 110))
    bg.alpha_composite(glow.filter(ImageFilter.GaussianBlur(160)))

    def put(item, cx, cy, size):
        cropped = item.resize((size, size), Image.LANCZOS)
        bg.alpha_composite(cropped, (int(cx - size / 2), int(cy - size / 2)))

    h = hoodie(NAVY, NAVY_FOLD, NAVY_HI)
    with_patch(h, 500, 470, 190)
    j = sweatshirt(GOLD, GOLD_FOLD, GOLD_HI)
    with_patch(j, 500, 440, 210)
    t = tshirt(CREAM, CREAM_FOLD, CREAM_HI)
    with_patch(t, 500, 470, 200)
    c = cap(NAVY, NAVY_FOLD, NAVY_HI)
    with_patch(c, 500, 400, 175)
    # NOTE: the garments are drawn on a navy ground here, so the navy hoodie gets a gold outline glow to stay readable
    put(j, 2380, 900, 1500)
    put(t, 1960, 1000, 1180)
    put(h, 2780, 980, 1300)
    put(c, 1560, 1250, 760)
    out = bg.convert("RGB").resize((1600, 900), Image.LANCZOS)
    out.save(os.path.join(OUT, "shop-hero.jpg"), "JPEG", quality=82, optimize=True, progressive=True)
    out.save(os.path.join(OUT, "shop-hero.webp"), "WEBP", quality=80, method=6)


if __name__ == "__main__":
    main()
