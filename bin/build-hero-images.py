#!/usr/bin/env python
"""
Make small WebP copies of the site photos that are used as banner / hero backgrounds, so phones download ~15-60 KB instead of the full photo.

    python bin/build-hero-images.py            (plain `python` with Pillow is enough; run it again after adding or replacing a photo)

    assets/img/site/photo-03.jpg  ->  assets/img/hero/site-photo-03-480.webp, site-photo-03-800.webp, ... (only widths that do not enlarge the photo)

The website picks them up by itself (helpers.php: responsive_sources()): a hero image whose file has copies in assets/img/hero/ is sent as
<picture> with a srcset; any other image (an upload from the admin) is sent exactly as before. Photos are darkened by the banner veil, so quality 72 is plenty.
"""
from pathlib import Path
from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent
SRC_DIRS = [ROOT / 'assets/img/site', ROOT / 'assets/img/shop']
OUT = ROOT / 'assets/img/hero'
WIDTHS = [480, 800, 1200, 1600]
SKIP = ('logo', 'icon', 'favicon', 'flag', 'sprite')


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    made = 0
    for base in SRC_DIRS:
        for f in sorted(base.rglob('*')):
            if f.suffix.lower() not in ('.jpg', '.jpeg', '.png') or any(s in f.name.lower() for s in SKIP):
                continue
            if base.name == 'shop' and 'hero' not in f.name.lower():
                continue                      # shop product photos are not banners
            rel = f.relative_to(ROOT / 'assets/img').with_suffix('')
            key = '-'.join(rel.parts)
            im = ImageOps.exif_transpose(Image.open(f)).convert('RGB')
            w, h = im.size
            sizes = [x for x in WIDTHS if x < w] + [w if w <= WIDTHS[-1] else WIDTHS[-1]]
            for target in sorted(set(sizes)):
                if target < 320:
                    continue
                out = OUT / f'{key}-{target}.webp'
                if out.exists() and out.stat().st_mtime >= f.stat().st_mtime:
                    continue
                img = im if target == w else im.resize((target, round(h * target / w)), Image.LANCZOS)
                img.save(out, 'WEBP', quality=72, method=6)
                made += 1
    print(f'{made} file(s) written to {OUT.relative_to(ROOT)}')


if __name__ == '__main__':
    main()
