#!/usr/bin/env python3
"""
update_gallery.py — Villa Corsu
--------------------------------
À exécuter à chaque fois que vous ajoutez/supprimez des photos dans le
dossier 'photos/'.

Usage :
    python3 update_gallery.py

Le script :
  1. Scanne le dossier 'photos/' (tous formats images, sans règle de nommage)
  2. Met à jour la galerie dans index_granted.html
  3. Affiche le nombre de photos trouvées

Placez ce script à la racine de votre site, au même niveau que index_granted.html.
"""

import os, re

# ── Config ───────────────────────────────────────────────────────────────────
PHOTOS_DIR   = "photos"
HTML_FILE    = "index_granted.html"
IMG_EXTS     = {".jpg", ".jpeg", ".png", ".webp", ".gif", ".avif", ".bmp"}
GALLERY_ID   = "villa-gallery-scroll"
MARKER_START = "<!-- photos chargées dynamiquement par update_gallery.py -->"
# ─────────────────────────────────────────────────────────────────────────────

def find_photos(folder):
    photos = []
    if not os.path.isdir(folder):
        print(f"⚠️  Dossier '{folder}' introuvable. Créez-le et placez-y vos photos.")
        return photos
    for fname in sorted(os.listdir(folder)):
        ext = os.path.splitext(fname)[1].lower()
        if ext in IMG_EXTS:
            photos.append(fname)
    return photos

def build_img_tags(photos):
    lines = []
    for fname in photos:
        src = f"{PHOTOS_DIR}/{fname}"
        name = os.path.splitext(fname)[0].replace("-", " ").replace("_", " ").title()
        lines.append(f'      <img class="villa-gallery-img" src="{src}" alt="{name}">')
    return "\n".join(lines)

def update_html(photos):
    with open(HTML_FILE, "r", encoding="utf-8") as f:
        html = f.read()

    # Find the scroll container and replace its content
    pattern = (
        r'(<div class="villa-gallery-scroll" id="villa-gallery-scroll">)'
        r'([\s\S]*?)'
        r'(</div>)'
    )
    img_tags = "\n" + build_img_tags(photos) + "\n    "
    new_block = r'\1' + img_tags + r'\3'
    updated, n = re.subn(pattern, new_block, html, count=1)

    if n == 0:
        print("❌ Balise villa-gallery-scroll introuvable dans index_granted.html")
        return False

    with open(HTML_FILE, "w", encoding="utf-8") as f:
        f.write(updated)
    return True

if __name__ == "__main__":
    photos = find_photos(PHOTOS_DIR)
    if not photos:
        print(f"ℹ️  Aucune photo trouvée dans '{PHOTOS_DIR}/'.")
    else:
        print(f"✅ {len(photos)} photo(s) trouvée(s) dans '{PHOTOS_DIR}/' :")
        for p in photos:
            print(f"   • {p}")
        if update_html(photos):
            print(f"\n✅ '{HTML_FILE}' mis à jour avec succès.")
        else:
            print(f"\n❌ Échec de la mise à jour de '{HTML_FILE}'.")
